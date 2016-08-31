<?php

namespace Kraken\_Module\Runtime;

use Kraken\_Module\Runtime\_Mock\Supervisor\ModelContinue;
use Kraken\Core\Core;
use Kraken\Core\CoreInterface;
use Kraken\Event\EventEmitter;
use Kraken\Event\EventEmitterInterface;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Loop\Loop;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeManager;
use Kraken\Runtime\RuntimeModel;
use Kraken\Runtime\RuntimeModelInterface;
use Kraken\Supervisor\SolverFactory;
use Kraken\Supervisor\Supervisor;
use Kraken\Test\TModule;
use Exception;

class RuntimeModelTest extends TModule
{
    /**
     *
     */
    public function testApiCreate_BehavesAsIntended()
    {
        $core    = $this->createCore();
        $emitter = $this->createEmitter();
        $loop    = $this->createLoop();
        $model   = $this->createModel($core, $emitter);

        $events   = [];
        $addEvent = function($name) use(&$events) {
            $events[] = $name;
        };
        $awaitEvent = function($name) use($emitter, $addEvent) {
            $emitter->on($name, function() use($name, $addEvent) {
                $addEvent($name);
            });
        };

        $awaitEvent('beforeCreate');
        $awaitEvent('create');
        $awaitEvent('afterCreate');
        $awaitEvent('beforeStart');
        $awaitEvent('start');
        $awaitEvent('afterStart');


        $emitter->once('create', function() use($loop) {
            $loop->stop();
        });

        $model->setState(Runtime::STATE_DESTROYED);
        $model->setLoop($loop);
        $model
            ->create()
            ->then($this->expectCallableOnce());

        $this->assertSame(Runtime::STATE_STARTED, $model->getState());
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

        unset($loop);
        unset($model);
        unset($emitter);
        unset($core);
    }

    /**
     *
     */
    public function testApiDestroy_BehavesAsIntended()
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
        $emitter = $this->createEmitter();
        $loop    = $this->createLoop();
        $model   = $this->createModel($core, $emitter, [ 'getRuntimeManager' ]);
        $model
            ->expects($this->atLeastOnce())
            ->method('getRuntimeManager')
            ->will($this->returnValue($manager));

        $events   = [];
        $addEvent = function($name) use(&$events) {
            $events[] = $name;
        };
        $awaitEvent = function($name) use($emitter, $addEvent) {
            $emitter->on($name, function() use($name, $addEvent) {
                $addEvent($name);
            });
        };

        $awaitEvent('beforeStop');
        $awaitEvent('stop');
        $awaitEvent('afterStop');
        $awaitEvent('beforeDestroy');
        $awaitEvent('destroy');
        $awaitEvent('afterDestroy');

        $emitter->once('start', function() use($model) {
            $model
                ->destroy()
                ->then($this->expectCallableOnce());
        });

        $model->setLoop($loop);
        $model->create();

        $this->assertSame(Runtime::STATE_DESTROYED, $model->getState());
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

        unset($loop);
        unset($model);
        unset($emitter);
        unset($core);
    }

    /**
     *
     */
    public function testApiStart_BehavesAsIntended()
    {
        $core    = $this->createCore();
        $emitter = $this->createEmitter();
        $model   = $this->createModel($core, $emitter);

        $events   = [];
        $addEvent = function($name) use(&$events) {
            $events[] = $name;
        };
        $awaitEvent = function($name) use($emitter, $addEvent) {
            $emitter->on($name, function() use($name, $addEvent) {
                $addEvent($name);
            });
        };

        $awaitEvent('beforeStart');
        $awaitEvent('start');
        $awaitEvent('afterStart');

        $model->setState(Runtime::STATE_CREATED);
        $model
            ->start()
            ->then($this->expectCallableOnce());

        $this->assertSame(Runtime::STATE_STARTED, $model->getState());
        $this->assertSame(
            [
                'beforeStart',
                'start',
                'afterStart'
            ],
            $events
        );

        unset($model);
        unset($emitter);
        unset($core);
    }

    /**
     *
     */
    public function testApiStop_BehavesAsIntended()
    {
        $core    = $this->createCore();
        $emitter = $this->createEmitter();
        $model   = $this->createModel($core, $emitter);

        $events   = [];
        $addEvent = function($name) use(&$events) {
            $events[] = $name;
        };
        $awaitEvent = function($name) use($emitter, $addEvent) {
            $emitter->on($name, function() use($name, $addEvent) {
                $addEvent($name);
            });
        };

        $awaitEvent('beforeStop');
        $awaitEvent('stop');
        $awaitEvent('afterStop');

        $model->setState(Runtime::STATE_STARTED);
        $model
            ->stop()
            ->then($this->expectCallableOnce());

        $this->assertSame(Runtime::STATE_STOPPED, $model->getState());
        $this->assertSame(
            [
                'beforeStop',
                'stop',
                'afterStop'
            ],
            $events
        );

        unset($model);
        unset($emitter);
        unset($core);
    }

    /**
     *
     */
    public function testCaseFailAndSucceed_BehavesAsIntended()
    {
        $core    = $this->createCore();
        $emitter = $this->createEmitter();
        $loop    = $this->createLoop();
        $model   = $this->createModel($core, $emitter);

        $queue = [];
        $super = $this->createSupervisor($model);
        $super->setSolver(Exception::class, new ModelContinue([ 'model' => $model, 'queue' => &$queue ]));

        $emitter->on('start', function() use($loop, &$queue) {
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
        unset($emitter);
        unset($core);
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
     * @param RuntimeModelInterface $model
     * @param string[]|null $methods
     * @return Supervisor|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSupervisor($model, $methods = null)
    {
        $factory = new SolverFactory();
        $factory
            ->define(ModelContinue::class, function($context = []) use($model) {
                return new ModelContinue(array_merge(
                    [ 'model' => $model ],
                    $context
                ));
            })
        ;

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
     * @param string[]|null $methods
     * @return EventEmitter|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createEmitter($methods = null)
    {
        return $this->getMock(EventEmitter::class, $methods);
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
