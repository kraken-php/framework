<?php

namespace Kraken\Root\Runtime\Provider;

use Kraken\Channel\Extra\Response;
use Kraken\Channel\Protocol\ProtocolInterface;
use Kraken\Channel\Router\RuleHandle\RuleHandler;
use Kraken\Channel\Router\RuleMatch\RuleMatchDestination;
use Kraken\Channel\Channel;
use Kraken\Channel\ChannelInterface;
use Kraken\Channel\ChannelCompositeInterface;
use Kraken\Runtime\Command\CommandManagerInterface;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
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
        'Kraken\Runtime\Command\CommandManagerInterface',
        'Kraken\Config\ConfigInterface',
        'Kraken\Channel\ChannelFactoryInterface',
        'Kraken\Runtime\RuntimeContainerInterface',
        'Kraken\Runtime\RuntimeContainerInterface',
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\Service\ChannelInternal'
    ];

    /**
     * @var CommandManagerInterface
     */
    protected $commander;

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $this->commander = $container->make('Kraken\Runtime\Command\CommandManagerInterface');

        $config  = $container->make('Kraken\Config\ConfigInterface');
        $runtime = $container->make('Kraken\Runtime\RuntimeContainerInterface');
        $factory = $container->make('Kraken\Channel\ChannelFactoryInterface');

        $master = $factory->create('Kraken\Channel\Channel', [
            $runtime->getParent() !== null
                ? $config->get('channel.channels.master.class')
                : 'Kraken\Channel\Model\Null\NullModel',
            array_merge(
                $config->get('channel.channels.master.config'),
                [
                    'host' => $runtime->getParent() !== null ? $runtime->getParent() : $runtime->getAlias()
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

        $container->instance(
            'Kraken\Runtime\Service\ChannelInternal',
            $composite
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        unset($this->commander);

        $container->remove(
            'Kraken\Runtime\Service\ChannelInternal'
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function boot(ContainerInterface $container)
    {
        $runtime = $container->make('Kraken\Runtime\RuntimeContainerInterface');
        $channel = $container->make('Kraken\Runtime\Service\ChannelInternal');
        $console = $container->make('Kraken\Runtime\Service\ChannelConsole');
        $loop    = $container->make('Kraken\Loop\LoopInterface');

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
        $router->addDefault(
            new RuleHandler(function($params) {
                return true;
            })
        );

        $router = $composite->getOutput();
        $router->addDefault(
            function($receiver, ProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($runtime, $slave, $master) {
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
        $router->addDefault(
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
        $router->addDefault(
            new RuleHandler(function($params) use($runtime, $slave, $master) {
                $master->push($runtime->getParent(), $params['protocol'], $params['flags']);
            })
        );

        $router = $master->getOutput();
        $router->addDefault(
            function($sender, ProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($master) {
                $master->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );

        $router = $slave->getOutput();
        $router->addDefault(
            function($sender, ProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($slave) {
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
        $router->addDefault(
            new RuleHandler(function($params) {
                return true;
            })
        );

        $router = $composite->getOutput();
        $router->addDefault(
            function($receiver, ProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($runtime, $slave, $console) {
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
        $router->addDefault(
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
        $router->addDefault(
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
        $router->addDefault(
            function($sender, ProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($master) {
                $master->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );

        $router = $slave->getOutput();
        $router->addDefault(
            function($sender, ProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0) use($slave) {
                $slave->push($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);
            }
        );
    }

    /**
     * @param ChannelCompositeInterface $composite
     * @param ProtocolInterface $protocol
     */
    private function executeProtocol(ChannelCompositeInterface $composite, ProtocolInterface $protocol)
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
