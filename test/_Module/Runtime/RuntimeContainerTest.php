<?php

namespace Kraken\_Module\Runtime;

use Kraken\_Module\Runtime\_Mock\Supervisor\ModelContinue;
use Kraken\Core\Core;
use Kraken\Core\CoreInterface;
use Kraken\Event\EventEmitterInterface;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Loop\Loop;
use Kraken\Runtime\Container\ProcessContainer;
use Kraken\Runtime\Container\ThreadContainer;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeContainer;
use Kraken\Runtime\RuntimeManager;
use Kraken\Runtime\RuntimeModel;
use Kraken\Supervisor\SolverFactory;
use Kraken\Supervisor\Supervisor;
use Kraken\Test\TModule;
use Exception;

class RuntimeContainerTest extends TModule
{
    /**
     * @dataProvider containerProvider
     * @param RuntimeContainer $container
     */
    public function testCaseCreate_BehavesAsIntended($container)
    {
        $core    = $this->createCore();
        $loop    = $this->createLoop();
        $model   = $container->getModel();

        $model->setCore($core);
        $model->setLoop($loop);

        $events   = [];
        $addEvent = function($name) use(&$events) {
            $events[] = $name;
        };
        $awaitEvent = function($name) use($container, $addEvent) {
            $container->on($name, function() use($name, $addEvent) {
                $addEvent($name);
            });
        };

        $awaitEvent('beforeCreate');
        $awaitEvent('create');
        $awaitEvent('afterCreate');
        $awaitEvent('beforeStart');
        $awaitEvent('start');
        $awaitEvent('afterStart');


        $container->once('create', function() use($loop) {
            $loop->stop();
        });

        $container
            ->create()
            ->then($this->expectCallableOnce());

        $this->assertSame(
            [
                'beforeCreate',
                'create',
                'afterCreate',
                'beforeStart',
                'start',
                'afterStart'
            ],
            $events
        );

        unset($container);
        unset($model);
        unset($loop);
        unset($core);
    }

    /**
     * @dataProvider containerProvider
     * @param RuntimeContainer $container
     */
    public function testApiDestroy_BehavesAsIntended($container)
    {
        $manager = $this->getMock(RuntimeManager::class, [], [], '', false);
        $manager
            ->expects($this->once())
            ->method('getRuntimes')
            ->will($this->returnValue([]));
        $manager
            ->expects($this->once())
            ->method('destroyRuntimes')
            ->with([], Runtime::DESTROY_FORCE)
            ->will($this->returnValue(null));

        $core    = $this->createCore();
        $loop    = $this->createLoop();
        $model   = $this->createModel($core, $container, [ 'getRuntimeManager' ]);
        $model
            ->expects($this->atLeastOnce())
            ->method('getRuntimeManager')
            ->will($this->returnValue($manager));

        $model->setCore($core);
        $model->setLoop($loop);

        $this->setProtectedProperty($container, 'model', $model);

        $events   = [];
        $addEvent = function($name) use(&$events) {
            $events[] = $name;
        };
        $awaitEvent = function($name) use($container, $addEvent) {
            $container->on($name, function() use($name, $addEvent) {
                $addEvent($name);
            });
        };

        $awaitEvent('beforeStop');
        $awaitEvent('stop');
        $awaitEvent('afterStop');
        $awaitEvent('beforeDestroy');
        $awaitEvent('destroy');
        $awaitEvent('afterDestroy');

        $container->once('start', function() use($container) {
            $container
                ->destroy()
                ->then($this->expectCallableOnce());
        });

        $container->create();

        $this->assertTrue($container->isDestroyed());
        $this->assertSame(
            [
                'beforeStop',
                'stop',
                'afterStop',
                'beforeDestroy',
                'destroy',
                'afterDestroy'
            ],
            $events
        );

        unset($container);
        unset($model);
        unset($loop);
        unset($core);
    }

    /**
     * @dataProvider containerProvider
     * @param RuntimeContainer $container
     */
    public function testApiStart_BehavesAsIntended($container)
    {
        $core    = $this->createCore();
        $loop    = $this->createLoop();
        $model   = $container->getModel();

        $model->setCore($core);
        $model->setLoop($loop);

        $events   = [];
        $addEvent = function($name) use(&$events) {
            $events[] = $name;
        };
        $awaitEvent = function($name) use($container, $addEvent) {
            $container->on($name, function() use($name, $addEvent) {
                $addEvent($name);
            });
        };

        $awaitEvent('beforeStart');
        $awaitEvent('start');
        $awaitEvent('afterStart');

        $model->setState(Runtime::STATE_CREATED);
        $container
            ->start()
            ->then($this->expectCallableOnce());

        $this->assertTrue($container->isStarted());
        $this->assertSame(
            [
                'beforeStart',
                'start',
                'afterStart'
            ],
            $events
        );

        unset($container);
        unset($model);
        unset($loop);
        unset($core);
    }

    /**
     * @dataProvider containerProvider
     * @param RuntimeContainer $container
     */
    public function testApiStop_BehavesAsIntended($container)
    {
        $core    = $this->createCore();
        $loop    = $this->createLoop();
        $model   = $container->getModel();

        $model->setCore($core);
        $model->setLoop($loop);

        $events   = [];
        $addEvent = function($name) use(&$events) {
            $events[] = $name;
        };
        $awaitEvent = function($name) use($container, $addEvent) {
            $container->on($name, function() use($name, $addEvent) {
                $addEvent($name);
            });
        };

        $awaitEvent('beforeStop');
        $awaitEvent('stop');
        $awaitEvent('afterStop');

        $model->setState(Runtime::STATE_STARTED);
        $container
            ->stop()
            ->then($this->expectCallableOnce());

        $this->assertTrue($container->isStopped());
        $this->assertSame(
            [
                'beforeStop',
                'stop',
                'afterStop'
            ],
            $events
        );

        unset($container);
        unset($model);
        unset($loop);
        unset($core);
    }

    /**
     * @dataProvider containerProvider
     * @param RuntimeContainer $container
     */
    public function testCaseFailAndSucceed_BehavesAsIntended($container)
    {
        $core    = $this->createCore();
        $loop    = $this->createLoop();
        $model   = $container->getModel();

        $model->setCore($core);
        $model->setLoop($loop);

        $queue = [];
        $super = $this->createSupervisor();
        $super->setHandler(Exception::class, new ModelContinue([ 'model' => $model, 'queue' => &$queue ]));

        $container->on('start', function() use($loop, &$queue) {
            $loop->onTick(function() use(&$queue) {
                $queue[] = 'A';
                throw new Exception('Some random uncatched exception');
            });
            $loop->onTick(function() use($loop, &$queue) {
                $queue[] = 'B';
                $loop->stop();
            });
        });

        $model->setSupervisor($super);
        $model->setLoop($loop);
        $model->create();

        $this->assertSame('ACB', implode('', $queue));

        unset($super);
        unset($model);
        unset($loop);
        unset($core);
    }

    /**
     * @return RuntimeContainer[][]
     */
    public function containerProvider()
    {
        return [
            [ $this->createProcessContainer() ],
            [ $this->createThreadContainer() ]
        ];
    }

    /**
     * @return ProcessContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createProcessContainer()
    {
        return $this->getMockForAbstractClass(ProcessContainer::class, [ 'parent', 'alias', 'name' ]);
    }

    /**
     * @return ThreadContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createThreadContainer()
    {
        return $this->getMockForAbstractClass(ProcessContainer::class, [ 'parent', 'alias', 'name' ]);
    }

    /**
     * @param string[]|null $methods
     * @return Loop|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createLoop($methods = null)
    {
        $select = new SelectLoop();
        return $this->getMock(Loop::class, $methods, [ $select ]);
    }

    /**
     * @param string[]|null $methods
     * @return Supervisor|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSupervisor($methods = null)
    {
        $factory = new SolverFactory();

        return $this->getMock(Supervisor::class, $methods, [ $factory ]);
    }

    /**
     * @param string[]|null $methods
     * @return Core|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createCore($methods = [])
    {
        return $this->getMock(Core::class, $methods);
    }

    /**
     * @param EventEmitterInterface $emitter
     * @param string[]|null $methods
     * @return RuntimeModel|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createModel(CoreInterface $core, EventEmitterInterface $emitter, $methods = null)
    {
        $model = $this->getMock(RuntimeModel::class, $methods, [ 'parent', 'alias', 'name' ]);

        $model->setCore($core);
        $model->setEventEmitter($emitter);

        return $model;
    }
}
