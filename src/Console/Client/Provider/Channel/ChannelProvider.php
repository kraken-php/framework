<?php

namespace Kraken\Console\Client\Provider\Channel;

use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\Router\RuleHandler;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Runtime\Runtime;

class ChannelProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface',
        'Kraken\Console\Client\ConsoleClientInterface',
        'Kraken\Channel\ChannelFactoryInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Console\Client\Channel\ConsoleInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $factory = $core->make('Kraken\Channel\ChannelFactoryInterface');
        $config  = $core->make('Kraken\Config\ConfigInterface');
        $console = $core->make('Kraken\Console\Client\ConsoleClientInterface');

        $channel = $factory->create('Kraken\Channel\ChannelBase', [
            $config->get('channel.channels.console.class'),
            array_merge(
                $config->get('channel.channels.console.config'),
                [ 'hosts' => Runtime::RESERVED_CONSOLE_SERVER ]
            )
        ]);

        $this->applyConsoleController($channel);

        $console->onStart(function() use($channel) {
            $channel->start();
        });
        $console->onStop(function() use($channel) {
            $channel->stop();
        });

        $core->instance(
            'Kraken\Console\Client\Channel\ConsoleInterface',
            $channel
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Console\Client\Channel\ConsoleInterface'
        );
    }

    /**
     * @param ChannelBaseInterface $channel
     */
    protected function applyConsoleController(ChannelBaseInterface $channel)
    {
        $router = $channel->input();
        $router->addAnchor(
            new RuleHandler(function($params) {})
        );

        $router = $channel->output();
        $router->addAnchor(
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
