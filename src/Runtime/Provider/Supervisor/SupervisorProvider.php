<?php

namespace Kraken\Runtime\Provider\Supervisor;

use Exception;
use Kraken\Config\ConfigInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Supervisor\SolverInterface;
use Kraken\Supervisor\SupervisorInterface;
use Kraken\Supervisor\SupervisorPluginInterface;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;

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
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');

        $errorManager    = $core->make('Kraken\Supervisor\SupervisorInterface', $config->get('error.manager.params'));
        $errorSupervisor = $core->make('Kraken\Supervisor\SupervisorInterface', $config->get('error.supervisor.params'));

        $core->instance(
            'Kraken\Runtime\Supervisor\SupervisorBaseInterface',
            $errorManager
        );

        $core->instance(
            'Kraken\Runtime\Supervisor\SupervisorRemoteInterface',
            $errorSupervisor
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Runtime\Supervisor\SupervisorBaseInterface'
        );

        $core->remove(
            'Kraken\Runtime\Supervisor\SupervisorRemoteInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');

        $baseSupervisor   = $core->make('Kraken\Runtime\Supervisor\SupervisorBaseInterface');
        $remoteSupervisor = $core->make('Kraken\Runtime\Supervisor\SupervisorRemoteInterface');

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
        $handlers = (array) $config->get('error.manager.handlers');

        $default = [
            'Kraken\Throwable\Exception\System\ChildUnresponsiveException'   => [ 'RuntimeRecreate', 'ContainerContinue' ],
            'Kraken\Throwable\Exception\System\ParentUnresponsiveException'  => 'ContainerDestroy',
            'Exception'                                                      => [ 'CmdLog', 'ContainerContinue' ]
        ];

        $plugins = (array) $config->get('error.manager.plugins');

        $this->bootBaseOrRemote($supervisor, $default, $handlers, $plugins);
    }

    /**
     * @param SupervisorInterface $supervisor
     * @param ConfigInterface $config
     * @throws Exception
     */
    private function bootRemoteSupervisor(SupervisorInterface $supervisor, ConfigInterface $config)
    {
        $handlers = (array) $config->get('error.supervisor.handlers');

        $default = [
            'Exception' => 'ContainerContinue'
        ];

        $plugins = (array) $config->get('error.supervisor.plugins');

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
        $this->setHandlers($supervisor, $handlers);
        $this->setHandlers($supervisor, $default);

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
    private function setHandlers(SupervisorInterface $supervisor, $handlers)
    {
        foreach ($handlers as $exception=>$handler)
        {
            $supervisor->setHandler($exception, $handler);
        }
    }
}
