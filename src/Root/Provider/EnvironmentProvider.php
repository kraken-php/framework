<?php

namespace Kraken\Root\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Environment\Environment;
use Kraken\Runtime\Runtime;
use Kraken\Throwable\ErrorHandler;

class EnvironmentProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Core\CoreInterface',
        'Kraken\Runtime\RuntimeContextInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Environment\EnvironmentInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $core    = $container->make('Kraken\Core\CoreInterface');
        $context = $container->make('Kraken\Runtime\RuntimeContextInterface');

        $env = new Environment($context, $core->getDataPath() . '/config.env/.env');

        $env->setOption('error_reporting', E_ALL);
        $env->setOption('log_errors', '1');
        $env->setOption('display_errors', '0');

        $env->registerErrorHandler([ 'Kraken\Throwable\ErrorHandler', 'handleError' ]);
        $env->registerShutdownHandler(function() use($context) {
            ErrorHandler::handleShutdown($context->getType() === Runtime::UNIT_PROCESS);
        });
        $env->registerExceptionHandler([ 'Kraken\Throwable\ExceptionHandler', 'handleException' ]);

        $container->instance(
            'Kraken\Environment\EnvironmentInterface',
            $env
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Environment\EnvironmentInterface'
        );
    }
}
