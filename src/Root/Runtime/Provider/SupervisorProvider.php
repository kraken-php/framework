<?php

namespace Kraken\Root\Runtime\Provider;

use Kraken\Config\ConfigInterface;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Supervisor\SolverInterface;
use Kraken\Supervisor\SupervisorInterface;
use Kraken\Supervisor\SupervisorPluginInterface;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Exception;

class SupervisorProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface',
        'Kraken\Supervisor\SupervisorInterface',
        'Kraken\Supervisor\SolverFactoryInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\Supervisor\SupervisorBaseInterface',
        'Kraken\Runtime\Supervisor\SupervisorRemoteInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $config = $container->make('Kraken\Config\ConfigInterface');

        $errorManager    = $container->make('Kraken\Supervisor\SupervisorInterface', [ null, $config->get('supervision.base.params') ]);
        $errorSupervisor = $container->make('Kraken\Supervisor\SupervisorInterface', [ null, $config->get('supervision.remote.params') ]);

        $container->instance(
            'Kraken\Runtime\Supervisor\SupervisorBaseInterface',
            $errorManager
        );

        $container->instance(
            'Kraken\Runtime\Supervisor\SupervisorRemoteInterface',
            $errorSupervisor
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Runtime\Supervisor\SupervisorBaseInterface'
        );

        $container->remove(
            'Kraken\Runtime\Supervisor\SupervisorRemoteInterface'
        );
    }

    /**
     * @param ContainerInterface $container
     * @throws Exception
     */
    protected function boot(ContainerInterface $container)
    {
        $config = $container->make('Kraken\Config\ConfigInterface');

        $baseSupervisor   = $container->make('Kraken\Runtime\Supervisor\SupervisorBaseInterface');
        $remoteSupervisor = $container->make('Kraken\Runtime\Supervisor\SupervisorRemoteInterface');

        $this->bootBaseSupervisor($baseSupervisor, $config);
        $this->bootRemoteSupervisor($remoteSupervisor, $config);
    }

    /**
     * @param SupervisorInterface $supervisor
     * @param ConfigInterface $config
     * @throws Exception
     */
    private function bootBaseSupervisor(SupervisorInterface $supervisor, ConfigInterface $config)
    {
        $handlers = (array) $config->get('supervision.base.handlers');

        $default = [
            $this->systemException('ChildUnresponsiveException')   => [ 'RuntimeRecreate', 'ContainerContinue' ],
            $this->systemException('ParentUnresponsiveException')  => [ 'ContainerDestroy' ],
            $this->systemError('FatalError')                       => [ 'CmdLog', 'ContainerDestroy' ],
            'Error'                                                => [ 'CmdLog', 'ContainerContinue' ],
            'Exception'                                            => [ 'CmdLog', 'ContainerContinue' ]
        ];

        $plugins = (array) $config->get('supervision.base.plugins');

        $this->bootBaseOrRemote($supervisor, $default, $handlers, $plugins);
    }

    /**
     * @param SupervisorInterface $supervisor
     * @param ConfigInterface $config
     * @throws Exception
     */
    private function bootRemoteSupervisor(SupervisorInterface $supervisor, ConfigInterface $config)
    {
        $handlers = (array) $config->get('supervision.remote.handlers');

        $default = [
            $this->systemError('FatalError')    => 'ContainerDestroy',
            'Error'                             => 'ContainerContinue',
            'Exception'                         => 'ContainerContinue'
        ];

        $plugins = (array) $config->get('supervision.remote.plugins');

        $this->bootBaseOrRemote($supervisor, $default, $handlers, $plugins);
    }

    /**
     * @param SupervisorInterface $supervisor
     * @param string[] $default
     * @param string[] $handlers
     * @param string[] $plugins
     * @throws Exception
     */
    private function bootBaseOrRemote($supervisor, $default = [], $handlers = [], $plugins = [])
    {
        $this->setSolvers($supervisor, $handlers);
        $this->setSolvers($supervisor, $default);

        foreach ($plugins as $pluginClass)
        {
            if (!class_exists($pluginClass))
            {
                throw new ResourceUndefinedException("SupervisorPlugin [$pluginClass] does not exist.");
            }

            $plugin = new $pluginClass();

            if (!($plugin instanceof SupervisorPluginInterface))
            {
                throw new InvalidArgumentException("SupervisorPlugin [$pluginClass] does not implement SupervisorPluginInterface.");
            }

            $plugin->registerPlugin($supervisor);
        }
    }

    /**
     * @param SupervisorInterface $supervisor
     * @param SolverInterface[]|string[]|string[][] $handlers
     */
    private function setSolvers(SupervisorInterface $supervisor, $handlers)
    {
        foreach ($handlers as $exception=>$handler)
        {
            $supervisor->setSolver($exception, $handler);
        }
    }

    /**
     * @param string $error
     * @return string
     */
    private function systemError($error)
    {
        return 'Kraken\Throwable\Error\\' . $error;
    }

    /**
     * @param string $exception
     * @return string
     */
    private function systemException($exception)
    {
        return 'Kraken\Throwable\Exception\System\\' . $exception;
    }
}
