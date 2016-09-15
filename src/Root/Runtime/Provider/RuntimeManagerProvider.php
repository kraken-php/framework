<?php

namespace Kraken\Root\Runtime\Provider;

use Kraken\Channel\ChannelCompositeInterface;
use Kraken\Config\ConfigInterface;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Loop\Timer\TimerCollection;
use Kraken\Runtime\Container\ProcessManagerFactory;
use Kraken\Runtime\Container\ThreadManagerFactory;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Runtime\RuntimeManager;
use Kraken\Runtime\RuntimeManagerFactoryInterface;
use Kraken\Runtime\RuntimeManagerInterface;
use Kraken\Throwable\Exception\System\ChildUnresponsiveException;
use Kraken\Throwable\Exception\System\ParentUnresponsiveException;
use Kraken\Util\Support\ArraySupport;
use Kraken\Util\System\SystemUnix;

class RuntimeManagerProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Core\CoreInterface',
        'Kraken\Config\ConfigInterface',
        'Kraken\Filesystem\FilesystemInterface',
        'Kraken\Runtime\RuntimeContainerInterface',
        'Kraken\Runtime\Service\ChannelInternal',
        'Kraken\Util\System\SystemInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\Container\ProcessManagerInterface',
        'Kraken\Runtime\Container\ThreadManagerInterface',
        'Kraken\Runtime\RuntimeManagerInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $system  = $container->make('Kraken\Util\System\SystemInterface');
        $core    = $container->make('Kraken\Core\CoreInterface');
        $config  = $container->make('Kraken\Config\ConfigInterface');
        $fs      = $container->make('Kraken\Filesystem\FilesystemInterface');
        $runtime = $container->make('Kraken\Runtime\RuntimeContainerInterface');
        $channel = $container->make('Kraken\Runtime\Service\ChannelInternal');

        $this->registerRuntimeSupervision($runtime, $channel, $config);

        $context = $config->exists('context') ? ArraySupport::flatten($config->get('context')) : [];

        $defaultConfig = [
            'runtime'    => $runtime,
            'channel'    => $channel,
            'context'    => $context,
            'system'     => $system,
            'filesystem' => $fs,
            'receiver'   => $runtime->getParent()
        ];

        $factoryProcess = new ProcessManagerFactory();
        $factoryThread  = new ThreadManagerFactory();

        if ($core->getType() === Runtime::UNIT_THREAD)
        {
            $factoryProcess->remove('Kraken\Runtime\Container\Manager\ProcessManagerBase');
        }

        $managerProcess = $this->createManager(
            $container,
            $factoryProcess,
            $defaultConfig,
            $config->get('runtime.manager.process')
        );

        $managerThread = $this->createManager(
            $container,
            $factoryThread,
            $defaultConfig,
            $config->get('runtime.manager.thread')
        );

        $managerRuntime = new RuntimeManager($channel, $managerProcess, $managerThread);

        $container->instance(
            'Kraken\Runtime\Container\ProcessManagerInterface',
            $managerProcess
        );

        $container->instance(
            'Kraken\Runtime\Container\ThreadManagerInterface',
            $managerThread
        );

        $container->instance(
            'Kraken\Runtime\RuntimeManagerInterface',
            $managerRuntime
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Runtime\Container\ProcessManagerInterface'
        );

        $container->remove(
            'Kraken\Runtime\Container\ThreadManagerInterface'
        );

        $container->remove(
            'Kraken\Runtime\RuntimeManagerInterface'
        );
    }

    /**
     * @param RuntimeContainerInterface $runtime
     * @param ChannelCompositeInterface $composite
     * @param ConfigInterface $config
     */
    private function registerRuntimeSupervision(RuntimeContainerInterface $runtime, ChannelCompositeInterface $composite, ConfigInterface $config)
    {
        $timerCollection = new TimerCollection();

        $channel = $composite->getBus('slave');

        $keepalive = $config->get('project.tolerance.child.keepalive');
        $channel->on('disconnect', function($alias) use($runtime, $keepalive, $timerCollection) {
            if ($keepalive <= 0)
            {
                return;
            }

            $timer = $runtime->getLoop()->addTimer($keepalive, function() use($alias, $runtime, $timerCollection) {
                $timerCollection->removeTimer($alias);
                $runtime->fail(
                    new ChildUnresponsiveException("Child runtime [$alias] is unresponsive."),
                    [ 'origin' => $alias ]
                );
            });

            $timerCollection->addTimer($alias, $timer);
        });
        $channel->on('connect', function($alias) use($timerCollection) {
            if (($timer = $timerCollection->getTimer($alias)) !== null)
            {
                $timer->cancel();
                $timerCollection->removeTimer($alias);
            }
        });

        $channel = $composite->getBus('master');

        $keepalive = $config->get('project.tolerance.parent.keepalive');
        $channel->on('disconnect', function($alias) use($runtime, $keepalive, $timerCollection) {
            if ($keepalive <= 0)
            {
                return;
            }

            $timer = $runtime->getLoop()->addTimer($keepalive, function() use($alias, $runtime, $timerCollection) {
                $timerCollection->removeTimer($alias);
                $runtime->fail(
                    new ParentUnresponsiveException("Parent runtime [$alias] is unresponsive."),
                    [ 'origin' => $alias ]
                );
            });

            $timerCollection->addTimer($alias, $timer);
        });
        $channel->on('connect', function($alias) use($timerCollection) {
            if (($timer = $timerCollection->getTimer($alias)) !== null)
            {
                $timer->cancel();
                $timerCollection->removeTimer($alias);
            }
        });
    }

    /**
     * @param ContainerInterface $container
     * @param RuntimeManagerFactoryInterface $managerFactory
     * @param mixed[] $default
     * @param mixed[] $config
     * @return RuntimeManagerInterface
     */
    private function createManager(ContainerInterface $container, RuntimeManagerFactoryInterface $managerFactory, $default, $config)
    {
        $managerClass = $config['class'];
        $managerConfig = array_merge($default, $config['config']);

        foreach ($managerConfig as $key=>$value)
        {
            if (is_string($value) && class_exists($value))
            {
                $managerConfig[$key] = $container->make($value);
            }
        }

        return $managerFactory->create($managerClass, [ $managerConfig ]);
    }
}
