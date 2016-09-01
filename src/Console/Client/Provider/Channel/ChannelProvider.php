<?php

namespace Kraken\Console\Client\Provider\Channel;

use Kraken\Channel\ChannelInterface;
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
        'Kraken\Console\Client\ClientInterface',
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
        $console = $core->make('Kraken\Console\Client\ClientInterface');

        $channel = $factory->create('Kraken\Channel\Channel', [
            $config->get('channel.channels.console.class'),
            array_merge(
                $config->get('channel.channels.console.config'),
                [ 'hosts' => Runtime::RESERVED_CONSOLE_SERVER ]
            )
        ]);

        $this->applyConsoleController($channel);

        $console->on('start', function() use($channel) {
            $channel->start();
        });
        $console->on('stop',  function() use($channel) {
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
     * @param ChannelInterface $channel
     */
    protected function applyConsoleController(ChannelInterface $channel)
    {
        $router = $channel->getInput();
        $router->addAnchor(
            new RuleHandler(function($params) {})
        );

        $router = $channel->getOutput();
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
