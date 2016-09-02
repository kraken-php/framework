<?php

namespace Kraken\Framework\Core\Provider;

use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Environment\Environment;

class EnvironmentProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Core\CoreInterface',
        'Kraken\Core\CoreInputContextInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Environment\EnvironmentInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $core    = $core->make('Kraken\Core\CoreInterface');
        $context = $core->make('Kraken\Core\CoreInputContextInterface');

        $env = new Environment($context, $core->getDataPath() . '/config.env/.env');

        $env->setOption('error_reporting', E_ALL);
        $env->setOption('log_errors', '1');
        $env->setOption('display_errors', '0');

        $env->registerErrorHandler([ 'Kraken\Throwable\ErrorHandler', 'handleError' ]);
        $env->registerShutdownHandler([ 'Kraken\Throwable\ErrorHandler', 'handleShutdown' ]);
        $env->registerExceptionHandler([ 'Kraken\Throwable\ExceptionHandler', 'handleException' ]);

        $core->instance(
            'Kraken\Environment\EnvironmentInterface',
            $env
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Environment\EnvironmentInterface'
        );
    }
}
