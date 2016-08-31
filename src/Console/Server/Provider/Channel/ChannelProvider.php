<?php

namespace Kraken\Console\Server\Provider\Channel;

use Kraken\Channel\Channel;
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
        'Kraken\Runtime\RuntimeContainerInterface'
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
            $config->get('channel.channels.master.class'),
            $config->get('channel.channels.master.config')
        ]);

        $slave = $factory->create('Kraken\Channel\Channel', [
            $config->get('channel.channels.slave.class'),
            array_merge(
                $config->get('channel.channels.slave.config'),
                [ 'name' => Runtime::RESERVED_CONSOLE_CLIENT ]
            )
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
        $loop    = $core->make('Kraken\Loop\LoopInterface');

        $this->applyConsoleRouting($runtime, $channel);

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
    private function applyConsoleRouting(RuntimeContainerInterface $runtime, ChannelCompositeInterface $composite)
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
            new RuleHandler(function($params) use($slave, $master) {
                $ch = ($params['alias'] === Runtime::RESERVED_CONSOLE_CLIENT) ? $master : $slave;
                $ch->push(
                    $params['alias'],
                    $params['protocol'],
                    $params['flags'],
                    $params['success'],
                    $params['failure'],
                    $params['cancel'],
                    $params['timeout']
                );
            })
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
        $router->addAnchor(
            new RuleHandler(function($params) use($runtime, $slave, $master) {
                $master->push(Runtime::RESERVED_CONSOLE_CLIENT, $params['protocol'], $params['flags']);
            })
        );

        $router = $master->getOutput();
        $router->addAnchor(
            new RuleHandler(function($params) use($master) {
                $protocol = $params['protocol'];
                $master->push(
                    $protocol->getDestination(),
                    $protocol,
                    $params['flags'],
                    $params['success'],
                    $params['failure'],
                    $params['cancel'],
                    $params['timeout']
                );
            })
        );

        $router = $slave->getOutput();
        $router->addAnchor(
            new RuleHandler(function($params) use($slave) {
                $protocol = $params['protocol'];
                $slave->push(
                    $protocol->getDestination(),
                    $protocol,
                    $params['flags'],
                    $params['success'],
                    $params['failure'],
                    $params['cancel'],
                    $params['timeout']
                );
            })
        );
    }

    /**
     * @param ChannelCompositeInterface $composite
     * @param ChannelProtocolInterface $protocol
     */
    private function executeProtocol(ChannelCompositeInterface $composite, ChannelProtocolInterface $protocol)
    {
        $params = json_decode($protocol->getMessage(), true);
        $command = array_shift($params);
        $params['origin'] = $protocol->getOrigin();
        $promise = $this->executeCommand($command, $params);

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
