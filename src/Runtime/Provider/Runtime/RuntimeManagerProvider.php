<?php

namespace Kraken\Runtime\Provider\Runtime;

use Kraken\Channel\ChannelCompositeInterface;
use Kraken\Config\ConfigInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Throwable\System\ChildUnresponsiveException;
use Kraken\Throwable\System\ParentUnresponsiveException;
use Kraken\Loop\Timer\TimerCollection;
use Kraken\Runtime\Process\ProcessManagerFactory;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeInterface;
use Kraken\Runtime\RuntimeManager;
use Kraken\Runtime\RuntimeManagerFactoryInterface;
use Kraken\Runtime\RuntimeManagerInterface;
use Kraken\Runtime\Thread\ThreadManagerFactory;
use Kraken\System\Unix\UnixSystem;

class RuntimeManagerProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface',
        'Kraken\Filesystem\FilesystemInterface',
        'Kraken\Runtime\RuntimeInterface',
        'Kraken\Runtime\RuntimeChannelInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Runtime\Process\ProcessManagerInterface',
        'Kraken\Runtime\Thread\ThreadManagerInterface',
        'Kraken\Runtime\RuntimeManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $system  = new UnixSystem();
        $config  = $core->make('Kraken\Config\ConfigInterface');
        $env     = $core->make('Kraken\Core\EnvironmentInterface');
        $fs      = $core->make('Kraken\Filesystem\FilesystemInterface');
        $runtime = $core->make('Kraken\Runtime\RuntimeInterface');
        $channel = $core->make('Kraken\Runtime\RuntimeChannelInterface');

        $this->registerRuntimeSupervision($runtime, $channel, $config);

        $defaultConfig = [
            'runtime'    => $runtime,
            'channel'    => $channel,
            'env'        => $env,
            'system'     => $system,
            'filesystem' => $fs,
            'receiver'   => $runtime->parent()
        ];

        $factoryProcess = new ProcessManagerFactory();
        $factoryThread = new ThreadManagerFactory();

        if ($core->unit() === Runtime::UNIT_THREAD)
        {
            $factoryProcess->remove('Kraken\Runtime\Process\Manager\ProcessManagerBase');
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
            'Kraken\Runtime\Process\ProcessManagerInterface',
            $managerProcess
        );

        $core->instance(
            'Kraken\Runtime\Thread\ThreadManagerInterface',
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
            'Kraken\Runtime\Process\ProcessManagerInterface'
        );

        $core->remove(
            'Kraken\Runtime\Thread\ThreadManagerInterface'
        );

        $core->remove(
            'Kraken\Runtime\RuntimeManagerInterface'
        );
    }

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelCompositeInterface $composite
     * @param ConfigInterface $config
     */
    private function registerRuntimeSupervision(RuntimeInterface $runtime, ChannelCompositeInterface $composite, ConfigInterface $config)
    {
        $timerCollection = new TimerCollection();

        $channel = $composite->bus('slave');
        $keepalive = $config->get('core.tolerance.child.keepalive');
        $channel->on('disconnect', function($alias) use($runtime, $keepalive, $timerCollection) {
            if ($keepalive <= 0)
            {
                return;
            }

            $timer = $runtime->loop()->addTimer($keepalive, function() use($alias, $runtime, $timerCollection) {
                $timerCollection->remove($alias);
                $runtime->fail(
                    new ChildUnresponsiveException("Child runtime [$alias] is unresponsive."),
                    [ 'origin' => $alias ]
                );
            });

            $timerCollection->add($alias, $timer);
        });
        $channel->on('connect', function($alias) use($timerCollection) {
            $timerCollection->get($alias)->cancel();
            $timerCollection->remove($alias);
        });

        $channel = $composite->bus('master');
        $keepalive = $config->get('core.tolerance.parent.keepalive');
        $channel->on('disconnect', function($alias) use($runtime, $keepalive, $timerCollection) {
            if ($keepalive <= 0)
            {
                return;
            }

            $timer = $runtime->loop()->addTimer($keepalive, function() use($alias, $runtime, $timerCollection) {
                $timerCollection->remove($alias);
                $runtime->fail(
                    new ParentUnresponsiveException("Parent runtime [$alias] is unresponsive."),
                    [ 'origin' => $alias ]
                );
            });

            $timerCollection->add($alias, $timer);
        });
        $channel->on('connect', function($alias) use($timerCollection) {
            $timerCollection->get($alias)->cancel();
            $timerCollection->remove($alias);
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
