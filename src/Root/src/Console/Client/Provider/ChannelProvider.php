<?php

namespace Kraken\Root\Console\Client\Provider;

use Kraken\Channel\ChannelInterface;
use Kraken\Channel\Router\RuleHandle\RuleHandler;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Runtime\Runtime;

class ChannelProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface',
        'Kraken\Console\Client\ClientInterface',
        'Kraken\Channel\ChannelFactoryInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Console\Client\Service\ChannelConsole'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $factory = $container->make('Kraken\Channel\ChannelFactoryInterface');
        $config  = $container->make('Kraken\Config\ConfigInterface');
        $console = $container->make('Kraken\Console\Client\ClientInterface');

        $channel = $factory->create('Kraken\Channel\Channel', [
            $config->get('channel.channels.console.class'),
            array_merge(
                $config->get('channel.channels.console.config'),
                [ 'host' => Runtime::RESERVED_CONSOLE_SERVER ]
            )
        ]);

        $this->applyConsoleController($channel);

        $console->on('start', function() use($channel) {
            $channel->start();
        });
        $console->on('stop',  function() use($channel) {
            $channel->stop();
        });

        $container->instance(
            'Kraken\Console\Client\Service\ChannelConsole',
            $channel
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Console\Client\Service\ChannelConsole'
        );
    }

    /**
     * @param ChannelInterface $channel
     */
    protected function applyConsoleController(ChannelInterface $channel)
    {
        $router = $channel->getInput();
        $router->addDefault(
            new RuleHandler(function($params) {})
        );

        $router = $channel->getOutput();
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
