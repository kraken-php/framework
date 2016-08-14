<?php

namespace Kraken\Core\Provider\Core;

use Kraken\Core\CoreInterface;
use Kraken\Core\Environment;
use Kraken\Core\EnvironmentInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;

class EnvironmentProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Core\CoreInputContextInterface',
        'Kraken\Config\ConfigInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Core\EnvironmentInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $context = $core->make('Kraken\Core\CoreInputContextInterface');
        $config  = $core->make('Kraken\Config\ConfigInterface');

        $env = new Environment($context, $config);

        $env->setOption('error_reporting', E_ALL);
        $env->setOption('log_errors', '1');
        $env->setOption('display_errors', '0');

        $inis = (array) $config->get('core.ini');
        foreach ($inis as $option=>$value)
        {
            $env->setOption($option, $value);
        }

        $this->setProcessProperties($env);

        $env->registerErrorHandler([ 'Kraken\Throwable\ErrorHandler', 'handleError' ]);
        $env->registerShutdownHandler([ 'Kraken\Throwable\ErrorHandler', 'handleShutdown' ]);
        $env->registerExceptionHandler([ 'Kraken\Throwable\ExceptionHandler', 'handleException' ]);

        $core->instance(
            'Kraken\Core\EnvironmentInterface',
            $env
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Core\EnvironmentInterface'
        );
    }

    /**
     * @param EnvironmentInterface $env
     */
    private function setProcessProperties(EnvironmentInterface $env)
    {
        $props = $env->getEnv('cli');
        if ($props['title'] !== 'php' && function_exists('cli_set_process_title'))
        {
            cli_set_process_title($props['title']);
        }
    }
}
