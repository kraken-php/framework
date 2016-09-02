<?php

namespace Kraken\Framework\Provider;

use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Runtime\Supervisor\SolverFactory;
use Kraken\Supervisor\SolverFactoryInterface;
use Kraken\Supervisor\Supervisor;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Util\Factory\FactoryPluginInterface;
use Exception;

class SupervisorProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Runtime\RuntimeContainerInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Supervisor\SolverFactoryInterface',
        'Kraken\Supervisor\SupervisorInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $runtime = $core->make('Kraken\Runtime\RuntimeContainerInterface');

        $factory = new SolverFactory($runtime);
        $config = [];

        $core->instance(
            'Kraken\Supervisor\SolverFactoryInterface',
            $factory
        );

        $core->factory(
            'Kraken\Supervisor\SupervisorInterface',
            function (SolverFactoryInterface $passedFactory = null, $passedConfig = [], $passedRules = []) use($factory, $config) {
                return new Supervisor(
                    $passedFactory !== null ? $passedFactory : $factory,
                    array_merge($config, $passedConfig),
                    $passedRules
                );
            }
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Supervisor\SolverFactoryInterface'
        );

        $core->remove(
            'Kraken\Supervisor\SupervisorInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config  = $core->make('Kraken\Config\ConfigInterface');
        $factory = $core->make('Kraken\Supervisor\SolverFactoryInterface');

        $handlers = (array) $config->get('error.handlers');
        foreach ($handlers as $handlerClass)
        {
            if (!class_exists($handlerClass))
            {
                throw new ResourceUndefinedException("Solver [$handlerClass] does not exist.");
            }

            $factory
                ->define($handlerClass, function($runtime, $context = []) use($handlerClass) {
                    return new $handlerClass($runtime, $context);
                });
        }

        $plugins = (array) $config->get('error.plugins');
        foreach ($plugins as $pluginClass)
        {
            if (!class_exists($pluginClass))
            {
                throw new ResourceUndefinedException("SupervisorPlugin [$pluginClass] does not exist.");
            }

            $plugin = new $pluginClass();

            if (!($plugin instanceof FactoryPluginInterface))
            {
                throw new InvalidArgumentException("SupervisorPlugin [$pluginClass] does not implement SupervisorPluginInterface.");
            }

            $plugin->registerPlugin($factory);
        }
    }
}
