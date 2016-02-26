<?php

namespace Kraken\Runtime\Container\Provider\Channel;

use Kraken\Channel\Channel;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\ChannelProtocolInterface;
use Kraken\Channel\ChannelCompositeInterface;
use Kraken\Channel\Extra\Response;
use Kraken\Channel\Router\RuleHandler;
use Kraken\Channel\Router\RuleMatchDestination;
use Kraken\Command\CommandManagerInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeInterface;
use Error;
use Exception;

class ChannelProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Command\CommandManagerInterface',
        'Kraken\Config\ConfigInterface',
        'Kraken\Channel\ChannelFactoryInterface',
        'Kraken\Runtime\RuntimeInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\RuntimeChannelInterface'
    ];

    /**
     * @var CommandManagerInterface
     */
    protected $commander;

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $this->commander = $core->make('Kraken\Command\CommandManagerInterface');

        $config  = $core->make('Kraken\Config\ConfigInterface');
        $runtime = $core->make('Kraken\Runtime\RuntimeInterface');
        $factory = $core->make('Kraken\Channel\ChannelFactoryInterface');

        $master = $factory->create('Kraken\Channel\ChannelBase', [
            $runtime->parent() !== null
                ? $config->get('channel.channels.master.class')
                : 'Kraken\Channel\Model\Null\NullModel',
            array_merge(
                $config->get('channel.channels.master.config'),
                [
                    'hosts' => $runtime->parent() !== null ? $runtime->parent() : $runtime->alias()
                ]
            )
        ]);

        $slave = $factory->create('Kraken\Channel\ChannelBase', [
            $config->get('channel.channels.slave.class'),
            $config->get('channel.channels.slave.config')
        ]);

        $composite = $factory->create('Kraken\Channel\ChannelComposite')
            ->setBus('master', $master)
            ->setBus('slave', $slave)
        ;

        $core->instance(
            'Kraken\Runtime\RuntimeChannelInterface',
            $composite
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        unset($this->commander);

        $core->remove(
            'Kraken\Runtime\RuntimeChannelInterface'
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $runtime = $core->make('Kraken\Runtime\RuntimeInterface');
        $channel = $core->make('Kraken\Runtime\RuntimeChannelInterface');
        $console = $core->make('Kraken\Runtime\RuntimeConsoleInterface');

//        $channel->on('connect', function($alias) {
//            echo "Connected [$alias]\n";
//        });
//        $channel->on('disconnect', function($alias) {
//            echo "Disconnected [$alias]\n";
//        });

        if ($runtime->parent() === null)
        {
            $this->applyRootRouting($runtime, $channel, $console);
        }
        else
        {
            $this->applySimpleRouting($runtime, $channel);
        }

        $runtime->on('create', [ $channel, 'start' ]);
        $runtime->on('destroy', [ $channel, 'stop' ]);
    }

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelCompositeInterface $composite
     */
    private function applySimpleRouting(RuntimeInterface $runtime, ChannelCompositeInterface $composite)
    {
        $master = $composite->bus('master');
        $slave  = $composite->bus('slave');

        $router = $composite->input();
        $router->addAnchor(
            new RuleHandler(function($params) {
                return true;
            })
        );

        $router = $composite->output();
        $router->addAnchor(
            function($receiver, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($runtime, $slave, $master) {
                if ($runtime->manager()->existsRuntime($receiver) || $slave->isConnected($receiver))
                {
                    $slave->push($receiver, $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
                else
                {
                    $master->push($runtime->parent(), $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
            }
        );

        $router = $master->input();
        $router->addRule(
            new RuleMatchDestination($master->name()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($slave) {
                $slave->push($slave->getConnected(), $params['protocol'], $params['flags']);
            })
        );

        $router = $slave->input();
        $router->addRule(
            new RuleMatchDestination($slave->name()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($runtime, $slave, $master) {
                $master->push($runtime->parent(), $params['protocol'], $params['flags']);
            })
        );

        $router = $master->output();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($master) {
                $master->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );

        $router = $slave->output();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($slave) {
                $slave->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );
    }

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelCompositeInterface $composite
     * @param ChannelBaseInterface $console
     */
    private function applyRootRouting(RuntimeInterface $runtime, ChannelCompositeInterface $composite, ChannelBaseInterface $console)
    {
        $master = $composite->bus('master');
        $slave  = $composite->bus('slave');

        $router = $composite->input();
        $router->addAnchor(
            new RuleHandler(function($params) {
                return true;
            })
        );

        $router = $composite->output();
        $router->addAnchor(
            function($receiver, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($runtime, $slave, $console) {
                if ($receiver === Runtime::RESERVED_CONSOLE_CLIENT || $protocol->getDestination() === Runtime::RESERVED_CONSOLE_CLIENT)
                {
                    $console->push(Runtime::RESERVED_CONSOLE_CLIENT, $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
                else if ($runtime->manager()->existsRuntime($receiver) || $slave->isConnected($receiver))
                {
                    $slave->push($receiver, $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
                else
                {
                    $slave->push($slave->getConnected(), $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
            }
        );

        $router = $master->input();
        $router->addRule(
            new RuleMatchDestination($master->name()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($slave) {
                $slave->push($slave->getConnected(), $params['protocol'], $params['flags']);
            })
        );

        $router = $slave->input();
        $router->addRule(
            new RuleMatchDestination($slave->name()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($runtime, $slave, $console) {
                $receiver = $params['alias'];
                $protocol = $params['protocol'];
                if ($receiver === Runtime::RESERVED_CONSOLE_CLIENT || $protocol->getDestination() === Runtime::RESERVED_CONSOLE_CLIENT)
                {
                    $console->push(Runtime::RESERVED_CONSOLE_CLIENT, $protocol, $params['flags']);
                }
                else
                {
                    $slave->push($slave->getConnected(), $params['protocol'], $params['flags']);
                }
            })
        );

        $router = $master->output();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($master) {
                $master->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );

        $router = $slave->output();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($slave) {
                $slave->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );
    }

    /**
     * @param ChannelCompositeInterface $composite
     * @param ChannelProtocolInterface $protocol
     */
    private function executeProtocol(ChannelCompositeInterface $composite, ChannelProtocolInterface $protocol)
    {
        /**
         * If the json_decode fails, it means the received message is leftover of request response,
         * hence it should be dropped.
         */
        try
        {
            $params = json_decode($protocol->getMessage(), true);
            $command = array_shift($params);
            $params['origin'] = $protocol->getOrigin();
            $promise = $this->executeCommand($command, $params);
        }
        catch (Error $ex)
        {
            return;
        }
        catch (Exception $ex)
        {
            return;
        }

        if ($protocol->getType() === Channel::TYPE_REQ)
        {
            $promise
                ->then(
                    function($response) use($composite, $protocol, $command) {
                        return (new Response($composite, $protocol, $response))->call();
                    },
                    function($reason) use($composite, $protocol) {
                        return (new Response($composite, $protocol, $reason))->call();
                    },
                    function($reason) use($composite, $protocol) {
                        return (new Response($composite, $protocol, $reason))->call();
                    }
                );
        }
    }

    /**
     * @param string $command
     * @param mixed[] $params
     * @return PromiseInterface
     */
    private function executeCommand($command, $params = [])
    {
        try
        {
            return $this->commander->execute($command, $params);
        }
        catch (Error $ex)
        {
            return Promise::doReject($ex);
        }
        catch (Exception $ex)
        {
            return Promise::doReject($ex);
        }
    }
}
