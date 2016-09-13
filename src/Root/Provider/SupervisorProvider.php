<?php

namespace Kraken\Root\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
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
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $runtime = $container->make('Kraken\Runtime\RuntimeContainerInterface');

        $factory = new SolverFactory($runtime);
        $config = [];

        $container->instance(
            'Kraken\Supervisor\SolverFactoryInterface',
            $factory
        );

        $container->factory(
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
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Supervisor\SolverFactoryInterface'
        );

        $container->remove(
            'Kraken\Supervisor\SupervisorInterface'
        );
    }

    /**
     * @param ContainerInterface $container
     * @throws Exception
     */
    protected function boot(ContainerInterface $container)
    {
        $config  = $container->make('Kraken\Config\ConfigInterface');
        $factory = $container->make('Kraken\Supervisor\SolverFactoryInterface');

        $handlers = (array) $config->get('supervision.solvers');
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

        $plugins = (array) $config->get('supervision.plugins');
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
