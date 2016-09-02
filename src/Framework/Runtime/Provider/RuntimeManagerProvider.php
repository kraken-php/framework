<?php

namespace Kraken\Framework\Runtime\Provider;

use Kraken\Channel\ChannelCompositeInterface;
use Kraken\Config\ConfigInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
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
use Kraken\Util\System\SystemUnix;

class RuntimeManagerProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface',
        'Kraken\Filesystem\FilesystemInterface',
        'Kraken\Runtime\RuntimeContainerInterface',
        'Kraken\Runtime\Service\ChannelInternal'
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
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $system  = new SystemUnix();
        $config  = $core->make('Kraken\Config\ConfigInterface');
        $fs      = $core->make('Kraken\Filesystem\FilesystemInterface');
        $runtime = $core->make('Kraken\Runtime\RuntimeContainerInterface');
        $channel = $core->make('Kraken\Runtime\Service\ChannelInternal');

        $this->registerRuntimeSupervision($runtime, $channel, $config);

        $defaultConfig = [
            'runtime'    => $runtime,
            'channel'    => $channel,
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
            $core,
            $factoryProcess,
            $defaultConfig,
            $config->get('runtime.manager.process')
        );

        $managerThread = $this->createManager(
            $core,
            $factoryThread,
            $defaultConfig,
            $config->get('runtime.manager.thread')
        );

        $managerRuntime = new RuntimeManager($managerProcess, $managerThread);

        $core->instance(
            'Kraken\Runtime\Container\ProcessManagerInterface',
            $managerProcess
        );

        $core->instance(
            'Kraken\Runtime\Container\ThreadManagerInterface',
            $managerThread
        );

        $core->instance(
            'Kraken\Runtime\RuntimeManagerInterface',
            $managerRuntime
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Runtime\Container\ProcessManagerInterface'
        );

        $core->remove(
            'Kraken\Runtime\Container\ThreadManagerInterface'
        );

        $core->remove(
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

        $keepalive = $config->get('core.tolerance.child.keepalive');
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

        $keepalive = $config->get('core.tolerance.parent.keepalive');
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
     * @param CoreInterface $core
     * @param RuntimeManagerFactoryInterface $managerFactory
     * @param mixed[] $default
     * @param mixed[] $config
     * @return RuntimeManagerInterface
     */
    private function createManager(CoreInterface $core, RuntimeManagerFactoryInterface $managerFactory, $default, $config)
    {
        $managerClass = $config['class'];
        $managerConfig = array_merge($default, $config['config']);

        foreach ($managerConfig as $key=>$value)
        {
            if (is_string($value) && class_exists($value))
            {
                $managerConfig[$key] = $core->make($value);
            }
        }

        return $managerFactory->create($managerClass, [ $managerConfig ]);
    }
}
