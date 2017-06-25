<?php

namespace Kraken\Root\Runtime\Provider;

use Dazzle\Channel\ChannelInterface;
use Dazzle\Channel\ChannelCompositeInterface;
use Dazzle\Channel\Router\RuleHandle\RuleHandler;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Runtime\Runtime;

class ChannelConsoleProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface',
        'Dazzle\Channel\ChannelFactoryInterface',
        'Kraken\Runtime\RuntimeContainerInterface',
        'Kraken\Runtime\Command\CommandManagerInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\Service\ChannelConsole'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $config  = $container->make('Kraken\Config\ConfigInterface');
        $factory = $container->make('Dazzle\Channel\ChannelFactoryInterface');
        $runtime = $container->make('Kraken\Runtime\RuntimeContainerInterface');

        $console = $factory->create('Dazzle\Channel\Channel', [
            $runtime->getParent() === null
                ? $config->get('channel.channels.console.class')
                : 'Dazzle\Channel\Model\Null\NullModel',
            array_merge(
                $config->get('channel.channels.console.config'),
                [ 'host' => Runtime::RESERVED_CONSOLE_CLIENT ]
            )
        ]);

        $container->instance(
            'Kraken\Runtime\Service\ChannelConsole',
            $console
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Runtime\Service\ChannelConsole'
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
        $loop    = $container->make('Dazzle\Loop\LoopInterface');

        $this->applyConsoleRouting($channel, $console);

        $runtime->on('create',  function() use($console) {
            $console->start();
        });
        $runtime->on('destroy', function() use($loop, $console) {
            $loop->onTick(function() use($console) {
                $console->stop();
            });
        });
    }

    /**
     * @param ChannelCompositeInterface $channel
     * @param ChannelInterface $console
     */
    private function applyConsoleRouting(ChannelCompositeInterface $channel, ChannelInterface $console)
    {
        $master = $channel->getBus('master');

        $router = $console->getInput();
        $router->addDefault(
            new RuleHandler(function($params) use($master) {
                $master->receive(
                    $params['alias'],
                    $params['protocol']
                );
            })
        );

        $router = $console->getOutput();
        $router->addDefault(
            new RuleHandler(function($params) use($channel) {
                $channel->push(
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
    }
}
