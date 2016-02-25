<?php

namespace Kraken\Runtime\Provider\Error;

use Exception;
use Kraken\Config\ConfigInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Error\ErrorManagerInterface;
use Kraken\Error\ErrorManagerPluginInterface;
use Kraken\Throwable\Resource\ResourceUndefinedException;
use Kraken\Throwable\Runtime\InvalidArgumentException;

class ErrorProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface',
        'Kraken\Error\ErrorManagerInterface',
        'Kraken\Error\ErrorFactoryInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\RuntimeErrorManagerInterface',
        'Kraken\Runtime\RuntimeErrorSupervisorInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');

        $errorManager    = $core->make('Kraken\Error\ErrorManagerInterface', $config->get('error.manager.params'));
        $errorSupervisor = $core->make('Kraken\Error\ErrorManagerInterface', $config->get('error.supervisor.params'));

        $core->instance(
            'Kraken\Runtime\RuntimeErrorManagerInterface',
            $errorManager
        );

        $core->instance(
            'Kraken\Runtime\RuntimeErrorSupervisorInterface',
            $errorSupervisor
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Runtime\RuntimeErrorManagerInterface'
        );

        $core->remove(
            'Kraken\Runtime\RuntimeErrorSupervisorInterface'
        );
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {
        $config = $core->make('Kraken\Config\ConfigInterface');

        $errorManager    = $core->make('Kraken\Runtime\RuntimeErrorManagerInterface');
        $errorSupervisor = $core->make('Kraken\Runtime\RuntimeErrorSupervisorInterface');

        $this->bootErrorManager($errorManager, $config);
        $this->bootErrorSupervisor($errorSupervisor, $config);
    }

    /**
     * @param ErrorManagerInterface $manager
     * @param ConfigInterface $config
     * @throws Exception
     */
    private function bootErrorManager(ErrorManagerInterface $manager, ConfigInterface $config)
    {
        $handlers = (array) $config->get('error.manager.handlers');

        $default = [
            'Kraken\Throwable\System\ChildUnresponsiveException'   => [ 'RuntimeRecreate', 'ContainerContinue' ],
            'Kraken\Throwable\System\ParentUnresponsiveException'  => 'ContainerDestroy',
            'Exception'                                            => [ 'CmdLog', 'ContainerContinue' ]
        ];

        $plugins = (array) $config->get('error.manager.plugins');

        $this->bootErrorManagerOrSupervisor($manager, $default, $handlers, $plugins);
    }

    /**
     * @param ErrorManagerInterface $manager
     * @param ConfigInterface $config
     * @throws Exception
     */
    private function bootErrorSupervisor(ErrorManagerInterface $manager, ConfigInterface $config)
    {
        $handlers = (array) $config->get('error.supervisor.handlers');

        $default = [
            'Exception'                                            => 'ContainerContinue'
        ];

        $plugins = (array) $config->get('error.supervisor.plugins');

        $this->bootErrorManagerOrSupervisor($manager, $default, $handlers, $plugins);
    }

    /**
     * @param ErrorManagerInterface $manager
     * @param string[] $default
     * @param string[] $handlers
     * @param string[] $plugins
     * @throws Exception
     */
    private function bootErrorManagerOrSupervisor($manager, $default = [], $handlers = [], $plugins = [])
    {
        $this->setHandlers($manager, $handlers);
        $this->setHandlers($manager, $default);

        foreach ($plugins as $pluginClass)
        {
            if (!class_exists($pluginClass))
            {
                throw new ResourceUndefinedException("ErrorManagerPlugin [$pluginClass] does not exist.");
            }

            $plugin = new $pluginClass();

            if (!($plugin instanceof ErrorManagerPluginInterface))
            {
                throw new InvalidArgumentException("ErrorManagerPlugin [$pluginClass] does not implement ErrorManagerPluginInterface.");
            }

            $plugin->registerPlugin($manager);
        }
    }

    /**
     * @param ErrorManagerInterface $manager
     * @param ErrorHandlerInterface[]|string[]|string[][] $handlers
     */
    private function setHandlers(ErrorManagerInterface $manager, $handlers)
    {
        foreach ($handlers as $exception=>$handler)
        {
            $manager->setHandler($exception, $handler);
        }
    }
}
