<?php

namespace Kraken\Framework\Runtime\Provider;

use Kraken\Channel\Channel;
use Kraken\Channel\ChannelInterface;
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
use Kraken\Runtime\RuntimeContainerInterface;
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
        'Kraken\Runtime\RuntimeContainerInterface',
        'Kraken\Runtime\RuntimeContainerInterface',
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\Channel\ChannelInterface'
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
        $runtime = $core->make('Kraken\Runtime\RuntimeContainerInterface');
        $factory = $core->make('Kraken\Channel\ChannelFactoryInterface');

        $master = $factory->create('Kraken\Channel\Channel', [
            $runtime->getParent() !== null
                ? $config->get('channel.channels.master.class')
                : 'Kraken\Channel\Model\Null\NullModel',
            array_merge(
                $config->get('channel.channels.master.config'),
                [
                    'hosts' => $runtime->getParent() !== null ? $runtime->getParent() : $runtime->getAlias()
                ]
            )
        ]);

        $slave = $factory->create('Kraken\Channel\Channel', [
            $config->get('channel.channels.slave.class'),
            $config->get('channel.channels.slave.config')
        ]);

        $composite = $factory->create('Kraken\Channel\ChannelComposite')
            ->setBus('master', $master)
            ->setBus('slave', $slave)
        ;

        $core->instance(
            'Kraken\Runtime\Channel\ChannelInterface',
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
            'Kraken\Runtime\Channel\ChannelInterface'
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function boot(CoreInterface $core)
    {
        $runtime = $core->make('Kraken\Runtime\RuntimeContainerInterface');
        $channel = $core->make('Kraken\Runtime\Channel\ChannelInterface');
        $console = $core->make('Kraken\Runtime\Channel\ConsoleInterface');
        $loop    = $core->make('Kraken\Loop\LoopInterface');

        if ($runtime->getParent() === null)
        {
            $this->applyRootRouting($runtime, $channel, $console);
        }
        else
        {
            $this->applySimpleRouting($runtime, $channel);
        }

        $runtime->on('create',  function() use($channel) {
            $channel->start();
        });
        $runtime->on('destroy', function() use($loop, $channel) {
            $loop->onTick(function() use($channel) {
                $channel->stop();
            });
        });
    }

    /**
     * @param RuntimeContainerInterface $runtime
     * @param ChannelCompositeInterface $composite
     */
    private function applySimpleRouting(RuntimeContainerInterface $runtime, ChannelCompositeInterface $composite)
    {
        $master = $composite->getBus('master');
        $slave  = $composite->getBus('slave');

        $router = $composite->getInput();
        $router->addAnchor(
            new RuleHandler(function($params) {
                return true;
            })
        );

        $router = $composite->getOutput();
        $router->addAnchor(
            function($receiver, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($runtime, $slave, $master) {
                if ($runtime->getManager()->existsRuntime($receiver) || $slave->isConnected($receiver))
                {
                    $slave->push($receiver, $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
                else
                {
                    $master->push($runtime->getParent(), $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
            }
        );

        $router = $master->getInput();
        $router->addRule(
            new RuleMatchDestination($master->getName()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($slave) {
                $slave->push($slave->getConnected(), $params['protocol'], $params['flags']);
            })
        );

        $router = $slave->getInput();
        $router->addRule(
            new RuleMatchDestination($slave->getName()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($runtime, $slave, $master) {
                $master->push($runtime->getParent(), $params['protocol'], $params['flags']);
            })
        );

        $router = $master->getOutput();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($master) {
                $master->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );

        $router = $slave->getOutput();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($slave) {
                $slave->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );
    }

    /**
     * @param RuntimeContainerInterface $runtime
     * @param ChannelCompositeInterface $composite
     * @param ChannelInterface $console
     */
    private function applyRootRouting(RuntimeContainerInterface $runtime, ChannelCompositeInterface $composite, ChannelInterface $console)
    {
        $master = $composite->getBus('master');
        $slave  = $composite->getBus('slave');

        $router = $composite->getInput();
        $router->addAnchor(
            new RuleHandler(function($params) {
                return true;
            })
        );

        $router = $composite->getOutput();
        $router->addAnchor(
            function($receiver, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($runtime, $slave, $console) {
                if ($receiver === Runtime::RESERVED_CONSOLE_CLIENT || $protocol->getDestination() === Runtime::RESERVED_CONSOLE_CLIENT)
                {
                    $console->push(Runtime::RESERVED_CONSOLE_CLIENT, $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
                else if ($runtime->getManager()->existsRuntime($receiver) || $slave->isConnected($receiver))
                {
                    $slave->push($receiver, $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
                else
                {
                    $slave->push($slave->getConnected(), $protocol, $flags, $success, $failure, $cancel, $timeout);
                }
            }
        );

        $router = $master->getInput();
        $router->addRule(
            new RuleMatchDestination($master->getName()),
            new RuleHandler(function($params) use($composite) {
                $this->executeProtocol($composite, $params['protocol']);
            })
        );
        $router->addAnchor(
            new RuleHandler(function($params) use($slave) {
                $slave->push($slave->getConnected(), $params['protocol'], $params['flags']);
            })
        );

        $router = $slave->getInput();
        $router->addRule(
            new RuleMatchDestination($slave->getName()),
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

        $router = $master->getOutput();
        $router->addAnchor(
            function($sender, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($master) {
                $master->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );

        $router = $slave->getOutput();
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
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
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
