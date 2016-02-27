<?php

namespace Kraken\Runtime\Provider\Error;

use Exception;
use Kraken\Config\ConfigInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Supervision\SolverInterface;
use Kraken\Supervision\SupervisorInterface;
use Kraken\Supervision\SupervisorPluginInterface;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;

class ErrorProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface',
        'Kraken\Supervision\SupervisorInterface',
        'Kraken\Supervision\SolverFactoryInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\Supervision\Base\SupervisionManagerInterface',
        'Kraken\Runtime\Supervision\Remote\SupervisionManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');

        $errorManager    = $core->make('Kraken\Supervision\SupervisorInterface', $config->get('error.manager.params'));
        $errorSupervisor = $core->make('Kraken\Supervision\SupervisorInterface', $config->get('error.supervisor.params'));

        $core->instance(
            'Kraken\Runtime\Supervision\Base\SupervisionManagerInterface',
            $errorManager
        );

        $core->instance(
            'Kraken\Runtime\Supervision\Remote\SupervisionManagerInterface',
            $errorSupervisor
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Runtime\Supervision\Base\SupervisionManagerInterface'
        );

        $core->remove(
            'Kraken\Runtime\Supervision\Remote\SupervisionManagerInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');

        $errorManager    = $core->make('Kraken\Runtime\Supervision\Base\SupervisionManagerInterface');
        $errorSupervisor = $core->make('Kraken\Runtime\Supervision\Remote\SupervisionManagerInterface');

        $this->bootSupervisor($errorManager, $config);
        $this->bootErrorSupervisor($errorSupervisor, $config);
    }

    /**
     * @param SupervisorInterface $manager
     * @param ConfigInterface $config
     * @throws Exception
     */
    private function bootSupervisor(SupervisorInterface $manager, ConfigInterface $config)
    {
        $handlers = (array) $config->get('error.manager.handlers');

        $default = [
            'Kraken\Throwable\Exception\System\ChildUnresponsiveException'   => [ 'RuntimeRecreate', 'ContainerContinue' ],
            'Kraken\Throwable\Exception\System\ParentUnresponsiveException'  => 'ContainerDestroy',
            'Exception'                                                      => [ 'CmdLog', 'ContainerContinue' ]
        ];

        $plugins = (array) $config->get('error.manager.plugins');

        $this->bootSupervisorOrSupervisor($manager, $default, $handlers, $plugins);
    }

    /**
     * @param SupervisorInterface $manager
     * @param ConfigInterface $config
     * @throws Exception
     */
    private function bootErrorSupervisor(SupervisorInterface $manager, ConfigInterface $config)
    {
        $handlers = (array) $config->get('error.supervisor.handlers');

        $default = [
            'Exception' => 'ContainerContinue'
        ];

        $plugins = (array) $config->get('error.supervisor.plugins');

        $this->bootSupervisorOrSupervisor($manager, $default, $handlers, $plugins);
    }

    /**
     * @param SupervisorInterface $manager
     * @param string[] $default
     * @param string[] $handlers
     * @param string[] $plugins
     * @throws Exception
     */
    private function bootSupervisorOrSupervisor($manager, $default = [], $handlers = [], $plugins = [])
    {
        $this->setHandlers($manager, $handlers);
        $this->setHandlers($manager, $default);

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

            $plugin->registerPlugin($manager);
        }
    }

    /**
     * @param SupervisorInterface $manager
     * @param SolverInterface[]|string[]|string[][] $handlers
     */
    private function setHandlers(SupervisorInterface $manager, $handlers)
    {
        foreach ($handlers as $exception=>$handler)
        {
            $manager->setHandler($exception, $handler);
        }
    }
}
