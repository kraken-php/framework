<?php

namespace Kraken\_Unit\Runtime;

use Kraken\Core\Core;
use Dazzle\Event\EventEmitter;
use Dazzle\Loop\Loop;
use Dazzle\Loop\LoopInterface;
use Dazzle\Loop\Model\SelectLoop;
use Dazzle\Promise\PromiseFulfilled;
use Dazzle\Promise\PromiseRejected;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeManager;
use Kraken\Runtime\RuntimeModel;
use Kraken\Runtime\RuntimeModelInterface;
use Kraken\Supervision\Supervisor;
use Dazzle\Throwable\Exception\LogicException;
use Dazzle\Throwable\Exception\Runtime\RejectionException;
use Kraken\Test\TUnit;
use Exception;

class RuntimeModelTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $runtime = $this->createModel();

        $this->assertInstanceOf(RuntimeModel::class, $runtime);
        $this->assertInstanceOf(RuntimeModelInterface::class, $runtime);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $runtime = $this->createModel();
        unset($runtime);
    }

    /**
     *
     */
    public function testApiGetType_ReturnsType()
    {
        $runtime = $this->createModel();

        $this->assertSame(Runtime::UNIT_UNDEFINED, $runtime->getType());
    }

    /**
     *
     */
    public function testApiGetParent_ReturnsParent()
    {
        $runtime = $this->createModel([ $parent = 'someParent' ]);

        $this->assertSame($parent, $runtime->getParent());
    }

    /**
     *
     */
    public function testApiGetAlias_ReturnsAlias()
    {
        $runtime = $this->createModel([ 'parent', $alias = 'someAlias' ]);

        $this->assertSame($alias, $runtime->getAlias());
    }

    /**
     *
     */
    public function testApiGetName_ReturnsName()
    {
        $args = [ 'arg1' => 'val1', 'arg2' => 'val2' ];
        $runtime = $this->createModel([ 'parent', 'alias', 'name', $args ]);

        $this->assertSame($args, $runtime->getArgs());
    }

    /**
     *
     */
    public function testApiGetArgs_ReturnsArgs()
    {
        $runtime = $this->createModel([ 'parent', 'alias', $name = 'someName' ]);

        $this->assertSame($name, $runtime->getName());
    }

    /**
     *
     */
    public function testApiGetCore_ReturnsNull_WhenCoreDoesNotExist()
    {
        $runtime = $this->createModel();

        $this->assertSame(null, $runtime->getCore());
    }

    /**
     *
     */
    public function testApiGetCore_ReturnsCore_WhenCoreDoesExist()
    {
        $core = $this->getMock(Core::class, [], [], '', false);

        $runtime = $this->createModel();
        $this->setProtectedProperty($runtime, 'core', $core);

        $this->assertSame($core, $runtime->getCore());
    }

    /**
     *
     */
    public function testApiSetCore_SetsCore()
    {
        $core = $this->getMock(Core::class, [], [], '', false);

        $runtime = $this->createModel();
        $runtime->setCore($core);

        $this->assertSame($core, $runtime->getCore());
    }

    /**
     *
     */
    public function testApiGetRuntimeManager_ReturnsNull_WhenManagerDoesNotExist()
    {
        $runtime = $this->createModel();

        $this->assertSame(null, $runtime->getRuntimeManager());
    }

    /**
     *
     */
    public function testApiGetRuntimeManager_ReturnsManager_WhenManagerDoesExist()
    {
        $manager = $this->getMock(RuntimeManager::class, [], [], '', false);

        $runtime = $this->createModel();
        $this->setProtectedProperty($runtime, 'manager', $manager);

        $this->assertSame($manager, $runtime->getRuntimeManager());
    }

    /**
     *
     */
    public function testApiSetRuntimeManager_SetsManager()
    {
        $manager = $this->getMock(RuntimeManager::class, [], [], '', false);

        $runtime = $this->createModel();
        $runtime->setRuntimeManager($manager);

        $this->assertSame($manager, $runtime->getRuntimeManager());
    }

    /**
     *
     */
    public function testApiGetLoop_ReturnsNull_WhenLoopDoesNotExist()
    {
        $runtime = $this->createModel();

        $this->assertSame(null, $runtime->getLoop());
    }

    /**
     *
     */
    public function testApiGetLoop_ReturnsLoop_WhenLoopDoesExist()
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);

        $runtime = $this->createModel();
        $this->setProtectedProperty($runtime, 'loop', $loop);

        $this->assertSame($loop, $runtime->getLoop());
    }

    /**
     *
     */
    public function testApiSetLoop_SetsLoop()
    {
        $model = $this->getMock(SelectLoop::class, [], [], '', false);
        $loop  = $this->getMock(Loop::class, [ 'getModel' ], [], '', false);
        $loop
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model));

        $runtime = $this->createModel();
        $runtime->setLoop($loop);

        $this->assertSame($loop, $this->getProtectedProperty($runtime, 'loop'));
        $this->assertInstanceOf(LoopInterface::class, $this->getProtectedProperty($runtime, 'loopBackup'));
    }

    /**
     *
     */
    public function testApiSetLoop_SetsNull()
    {
        $runtime = $this->createModel();
        $runtime->setLoop(null);

        $this->assertSame(null, $this->getProtectedProperty($runtime, 'loop'));
        $this->assertSame(null, $this->getProtectedProperty($runtime, 'loopBackup'));
    }

    /**
     *
     */
    public function testApiGetSupervisor_ReturnsNull_WhenSupervisorDoesNotExist()
    {
        $runtime = $this->createModel();

        $this->assertSame(null, $runtime->getSupervisor());
    }

    /**
     *
     */
    public function testApiGetSupervisor_ReturnsSupervisor_WhenSupervisorDoesExist()
    {
        $super = $this->getMock(Supervisor::class, [], [], '', false);

        $runtime = $this->createModel();
        $this->setProtectedProperty($runtime, 'supervisor', $super);

        $this->assertSame($super, $runtime->getSupervisor());
    }

    /**
     *
     */
    public function testApiSetSupervisor_SetsSupervisor()
    {
        $super = $this->getMock(Supervisor::class, [], [], '', false);

        $runtime = $this->createModel();
        $runtime->setSupervisor($super);

        $this->assertSame($super, $runtime->getSupervisor());

    }

    /**
     *
     */
    public function testApiSetSupervisor_SetsNull()
    {
        $runtime = $this->createModel();
        $runtime->setSupervisor(null);

        $this->assertSame(null, $runtime->getSupervisor());
    }

    /**
     *
     */
    public function testApiGetEventEmitter_ReturnsNull_WhenEventEmitterDoesNotExist()
    {
        $runtime = $this->createModel();

        $this->assertSame(null, $runtime->getEventEmitter());
    }

    /**
     *
     */
    public function testApiGetEventEmitter_ReturnsEventEmitter_WhenEventEmitterDoesExist()
    {
        $emitter = $this->getMock(EventEmitter::class, [], [], '', false);

        $runtime = $this->createModel();
        $this->setProtectedProperty($runtime, 'eventEmitter', $emitter);

        $this->assertSame($emitter, $runtime->getEventEmitter());
    }

    /**
     *
     */
    public function testApiSetEventEmitter_SetsEventEmitter()
    {
        $emitter = $this->getMock(EventEmitter::class, [], [], '', false);

        $runtime = $this->createModel();
        $runtime->setEventEmitter($emitter);

        $this->assertSame($emitter, $runtime->getEventEmitter());
    }

    /**
     *
     */
    public function testApiSetEventEmitter_SetsNull()
    {
        $runtime = $this->createModel();
        $runtime->setEventEmitter(null);

        $this->assertSame(null, $runtime->getEventEmitter());
    }

    /**
     *
     */
    public function testApiGetState_ReturnsState()
    {
        $runtime = $this->createModel();
        $states  = $this->getStates();

        foreach ($states as $state)
        {
            $this->setProtectedProperty($runtime, 'state', $state);
            $this->assertSame($state, $runtime->getState());
        }
    }

    /**
     *
     */
    public function testApiSetState_SetsState()
    {
        $runtime = $this->createModel();
        $states  = $this->getStates();

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertSame($state, $this->getProtectedProperty($runtime, 'state'));
        }
    }

    /**
     *
     */
    public function testApiIsState_ReturnsFalse_WhenStateDoesNotMatch()
    {
        $runtime = $this->createModel();
        $states  = $this->getStates();

        foreach ($states as $state)
        {
            $runtime->setState(!$state);
            $this->assertFalse($runtime->isState($state));
        }
    }

    /**
     *
     */
    public function testApiIsState_ReturnsTrue_WhenStateDoesMatch()
    {
        $runtime = $this->createModel();
        $states  = $this->getStates();

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertTrue($runtime->isState($state));
        }
    }

    /**
     *
     */
    public function testApiIsCreated_ReturnsFalse_WhenStateDoesNotMatch()
    {
        $runtime = $this->createModel();
        $states  = [ Runtime::STATE_STARTED, Runtime::STATE_STOPPED, Runtime::STATE_DESTROYED ];

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertFalse($runtime->isCreated());
        }
    }

    /**
     *
     */
    public function testApiIsCreated_ReturnsTrue_WhenStateDoesMatch()
    {
        $runtime = $this->createModel();
        $states  = [ Runtime::STATE_CREATED ];

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertTrue($runtime->isCreated());
        }
    }

    /**
     *
     */
    public function testApiIsStarted_ReturnsFalse_WhenStateDoesNotMatch()
    {
        $runtime = $this->createModel();
        $states  = [ Runtime::STATE_CREATED, Runtime::STATE_STOPPED, Runtime::STATE_DESTROYED ];

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertFalse($runtime->isStarted());
        }
    }

    /**
     *
     */
    public function testApiIsStarted_ReturnsTrue_WhenStateDoesMatch()
    {
        $runtime = $this->createModel();
        $states  = [ Runtime::STATE_STARTED ];

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertTrue($runtime->isStarted());
        }
    }

    /**
     *
     */
    public function testApiIsStopped_ReturnsFalse_WhenStateDoesNotMatch()
    {
        $runtime = $this->createModel();
        $states  = [ Runtime::STATE_CREATED, Runtime::STATE_STARTED, Runtime::STATE_DESTROYED ];

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertFalse($runtime->isStopped());
        }
    }

    /**
     *
     */
    public function testApiIsStopped_ReturnsTrue_WhenStateDoesMatch()
    {
        $runtime = $this->createModel();
        $states  = [ Runtime::STATE_STOPPED ];

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertTrue($runtime->isStopped());
        }
    }

    /**
     *
     */
    public function testApiIsDestroyed_ReturnsFalse_WhenStateDoesNotMatch()
    {
        $runtime = $this->createModel();
        $states  = [ Runtime::STATE_CREATED, Runtime::STATE_STARTED, Runtime::STATE_STOPPED ];

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertFalse($runtime->isDestroyed());
        }
    }

    /**
     *
     */
    public function testApiIsDestroyed_ReturnsTrue_WhenStateDoesMatch()
    {
        $runtime = $this->createModel();
        $states  = [ Runtime::STATE_DESTROYED ];

        foreach ($states as $state)
        {
            $runtime->setState($state);
            $this->assertTrue($runtime->isDestroyed());
        }
    }

    /**
     *
     */
    public function testApiIsFailed_ReturnsFalse_WhenStateDoesNotMatch()
    {
        $runtime = $this->createModel();
        $this->assertFalse($runtime->isFailed());
    }

    /**
     *
     */
    public function testApiIsFailed_ReturnsTrue_WhenStateDoesMatch()
    {
        $ex = new Exception;
        $params = [];

        $super = $this->getMock(Supervisor::class, [], [], '', false);
        $loop  = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('onTick');

        $runtime = $this->createModel([], [ 'getSupervisor', 'getLoop', 'setLoopState' ]);
        $runtime
            ->expects($this->once())
            ->method('getSupervisor')
            ->will($this->returnValue($super));
        $runtime
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));
        $runtime
            ->expects($this->once())
            ->method('setLoopState');

        $runtime->fail($ex, $params);

        $this->assertTrue($runtime->isFailed());
    }

    /**
     *
     */
    public function testApiCreate_RejectsPromise_WhenInvokedFromStateOtherThanCreatedOrDestroyed()
    {
        $runtime = $this->createModel();
        $states = [
            Runtime::STATE_STARTED,
            Runtime::STATE_STOPPED
        ];

        foreach ($states as $state)
        {
            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isInstanceOf(RejectionException::class));

            $runtime
                ->create()
                ->then(
                    $this->expectCallableNever(),
                    $callable
                );
        }
    }

    /**
     *
     */
    public function testApiCreate_ResolvesPromise_WhenInvokedFromStateCreated()
    {
        $runtime = $this->createModel();
        $states = [
            Runtime::STATE_CREATED
        ];

        foreach ($states as $state)
        {
            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isType('string'));

            $runtime
                ->create()
                ->then(
                    $callable,
                    $this->expectCallableNever()
                );
        }
    }

    /**
     *
     */
    public function testApiCreate_CallsStartAndResolvesPromise_WhenInvokedFromStateOtherThanCreatedOrDestroyedAndStartSucceeded()
    {
        $states = [
            Runtime::STATE_DESTROYED
        ];

        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('onTick')
            ->will($this->returnCallback(function($callable) {
                $callable();
            }));

        $events  = [];
        $emitter = $this->getMock(EventEmitter::class, [], [], '', false);
        $emitter
            ->expects($this->exactly(3))
            ->method('emit')
            ->will($this->returnCallback(function($event) use(&$events) {
                $events[] = $event;
            }));

        $runtime = $this->createModel([], [ 'getLoop', 'start', 'getEventEmitter', 'setLoopState', 'startLoop' ]);
        $runtime
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));
        $runtime
            ->expects($this->once())
            ->method('start')
            ->will($this->returnValue(new PromiseFulfilled()));
        $runtime
            ->expects($this->once())
            ->method('getEventEmitter')
            ->will($this->returnValue($emitter));
        $runtime
            ->expects($this->once())
            ->method('setLoopState');
        $runtime
            ->expects($this->once())
            ->method('startLoop');

        foreach ($states as $state)
        {
            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isType('string'));

            $runtime
                ->create()
                ->then(
                    $callable,
                    $this->expectCallableNever()
                );

            $this->assertSame([ 'beforeCreate', 'create', 'afterCreate' ], $events);
        }
    }

    /**
     *
     */
    public function testApiCreate_CallsStartAndRejectsPromise_WhenInvokedFromStateOtherThanCreatedOrDestroyedAndStartFailed()
    {
        $states = [
            Runtime::STATE_DESTROYED
        ];

        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('onTick')
            ->will($this->returnCallback(function($callable) {
                $callable();
            }));

        $events  = [];
        $emitter = $this->getMock(EventEmitter::class, [], [], '', false);
        $emitter
            ->expects($this->exactly(3))
            ->method('emit')
            ->will($this->returnCallback(function($event) use(&$events) {
                $events[] = $event;
            }));

        $runtime = $this->createModel([], [ 'getLoop', 'start', 'getEventEmitter', 'setLoopState', 'startLoop' ]);
        $runtime
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));
        $runtime
            ->expects($this->once())
            ->method('start')
            ->will($this->returnValue(new PromiseRejected($ex = new Exception)));
        $runtime
            ->expects($this->once())
            ->method('getEventEmitter')
            ->will($this->returnValue($emitter));
        $runtime
            ->expects($this->once())
            ->method('setLoopState');
        $runtime
            ->expects($this->once())
            ->method('startLoop');

        foreach ($states as $state)
        {
            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($ex);

            $runtime
                ->create()
                ->then(
                    $this->expectCallableNever(),
                    $callable
                );

            $this->assertSame([ 'beforeCreate', 'create', 'afterCreate' ], $events);
        }
    }

    /**
     *
     */
    public function testApiDestroy_ResolvesPromise_WhenInvokedFromStateDestroyed()
    {
        $runtime = $this->createModel();
        $states = [
            Runtime::STATE_DESTROYED
        ];

        foreach ($states as $state)
        {
            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isType('string'));

            $runtime
                ->destroy()
                ->then(
                    $callable,
                    $this->expectCallableNever()
                );
        }
    }

    /**
     *
     */
    public function testApiDestroy_CallsStopAndResolvesPromise_WhenInvokedFromStateOtherThanDestroyedAndStopSucceeded()
    {
        $states = [
            Runtime::STATE_CREATED,
            Runtime::STATE_STARTED,
            Runtime::STATE_STOPPED
        ];

        foreach ($states as $state)
        {
            $loop = $this->getMock(Loop::class, [], [], '', false);
            $loop
                ->expects($this->once())
                ->method('onTick')
                ->will($this->returnCallback(function($callable) {
                    $callable();
                }));

            $events  = [];
            $emitter = $this->getMock(EventEmitter::class, [], [], '', false);
            $emitter
                ->expects($this->exactly(3))
                ->method('emit')
                ->will($this->returnCallback(function($event) use(&$events) {
                    $events[] = $event;
                }));

            $runtimes = [ 'A', 'B' ];
            $manager  = $this->getMock(RuntimeManager::class, [], [], '', false);
            $manager
                ->expects($this->once())
                ->method('getRuntimes')
                ->will($this->returnValue($runtimes));
            $manager
                ->expects($this->once())
                ->method('destroyRuntimes')
                ->with($runtimes, Runtime::DESTROY_FORCE)
                ->will($this->returnValue(true));

            $runtime = $this->createModel(
                [],
                [ 'getLoop', 'stop', 'getRuntimeManager', 'getEventEmitter', 'setLoopState', 'stopLoop' ]
            );
            $runtime
                ->expects($this->once())
                ->method('getLoop')
                ->will($this->returnValue($loop));
            $runtime
                ->expects($this->once())
                ->method('stop')
                ->will($this->returnValue(new PromiseFulfilled()));
            $runtime
                ->expects($this->atLeastOnce())
                ->method('getRuntimeManager')
                ->will($this->returnValue($manager));
            $runtime
                ->expects($this->atLeastOnce())
                ->method('getEventEmitter')
                ->will($this->returnValue($emitter));
            $runtime
                ->expects($this->once())
                ->method('setLoopState');
            $runtime
                ->expects($this->once())
                ->method('stopLoop');

            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isType('string'));

            $runtime
                ->destroy()
                ->then(
                    $callable,
                    $this->expectCallableNever()
                );

            $this->assertSame([ 'beforeDestroy', 'destroy', 'afterDestroy' ], $events);
        }
    }

    /**
     *
     */
    public function testApiDestroy_CallsStopAndRejectsPromise_WhenInvokedFromStateOtherThanDestroyedAndStopFailed()
    {
        $states = [
            Runtime::STATE_CREATED,
            Runtime::STATE_STARTED,
            Runtime::STATE_STOPPED
        ];

        foreach ($states as $state)
        {
            $runtime = $this->createModel([], [ 'stop' ]);
            $runtime
                ->expects($this->once())
                ->method('stop')
                ->will($this->returnValue(new PromiseRejected($ex = new RejectionException())));

            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isInstanceOf(RejectionException::class));

            $runtime
                ->destroy()
                ->then(
                    $this->expectCallableNever(),
                    $callable
                );
        }
    }

    /**
     *
     */
    public function testApiStart_RejectsPromise_WhenInvokedFromStateDestroyed()
    {
        $runtime = $this->createModel();
        $states = [
            Runtime::STATE_DESTROYED
        ];

        foreach ($states as $state)
        {
            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isInstanceOf(RejectionException::class));

            $runtime
                ->start()
                ->then(
                    $this->expectCallableNever(),
                    $callable
                );
        }
    }

    /**
     *
     */
    public function testApiStart_ResolvesPromise_WhenInvokedFromStateStarted()
    {
        $runtime = $this->createModel();
        $states = [
            Runtime::STATE_STARTED
        ];

        foreach ($states as $state)
        {
            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isType('string'));

            $runtime
                ->start()
                ->then(
                    $callable,
                    $this->expectCallableNever()
                );
        }
    }

    /**
     *
     */
    public function testApiStart_ResolvesPromise_WhenInvokedFromStateCreatedOrStopped()
    {
        $states = [
            Runtime::STATE_CREATED,
            Runtime::STATE_STOPPED
        ];

        foreach ($states as $state)
        {
            $events  = [];
            $emitter = $this->getMock(EventEmitter::class, [], [], '', false);
            $emitter
                ->expects($this->exactly(3))
                ->method('emit')
                ->will($this->returnCallback(function($event) use(&$events) {
                    $events[] = $event;
                }));

            $runtime = $this->createModel([], [ 'getEventEmitter' ]);
            $runtime
                ->expects($this->once())
                ->method('getEventEmitter')
                ->will($this->returnValue($emitter));

            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isType('string'));

            $runtime
                ->start()
                ->then(
                    $callable,
                    $this->expectCallableNever()
                );

            $this->assertSame([ 'beforeStart', 'start', 'afterStart' ], $events);
        }
    }

    /**
     *
     */
    public function testApiStop_RejectsPromise_WhenInvokedFromStateCreatedOrDestroyed()
    {
        $runtime = $this->createModel();
        $states = [
            Runtime::STATE_CREATED,
            Runtime::STATE_DESTROYED
        ];

        foreach ($states as $state)
        {
            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isInstanceOf(RejectionException::class));

            $runtime
                ->stop()
                ->then(
                    $this->expectCallableNever(),
                    $callable
                );
        }
    }

    /**
     *
     */
    public function testApiStop_ResolvesPromise_WhenInvokedFromStateStopped()
    {
        $runtime = $this->createModel();
        $states = [
            Runtime::STATE_STOPPED
        ];

        foreach ($states as $state)
        {
            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isType('string'));

            $runtime
                ->stop()
                ->then(
                    $callable,
                    $this->expectCallableNever()
                );
        }
    }

    /**
     *
     */
    public function testApiStop_ResolvesPromiseAndEmitsEvents_WhenInvokedFromStateStarted()
    {
        $states = [
            Runtime::STATE_STARTED
        ];

        foreach ($states as $state)
        {
            $events  = [];
            $emitter = $this->getMock(EventEmitter::class, [], [], '', false);
            $emitter
                ->expects($this->exactly(3))
                ->method('emit')
                ->will($this->returnCallback(function($event) use(&$events) {
                    $events[] = $event;
                }));

            $runtime = $this->createModel([], [ 'getEventEmitter' ]);
            $runtime
                ->expects($this->once())
                ->method('getEventEmitter')
                ->will($this->returnValue($emitter));

            $runtime->setState($state);

            $callable = $this->createCallableMock();
            $callable
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->isType('string'));

            $runtime
                ->stop()
                ->then(
                    $callable,
                    $this->expectCallableNever()
                );

            $this->assertSame([ 'beforeStop', 'stop', 'afterStop' ], $events);
        }
    }

    /**
     *
     */
    public function testApiFail_StopsLoopAndDelegatesFailureToSupervisor()
    {
        $ex = new Exception;
        $params = [ 'param' => 'value' ];

        $super = $this->getMock(Supervisor::class, [], [], '', false);
        $super
            ->expects($this->any())
            ->method('solve')
            ->with($ex, $this->isType('array'))
            ->will($this->returnValue(new PromiseFulfilled()));

        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('onTick')
            ->will($this->returnCallback(function($callable) {
                $callable();
            }));

        $runtime = $this->createModel([], [ 'getSupervisor', 'getLoop', 'setLoopState' ]);
        $runtime
            ->expects($this->once())
            ->method('getSupervisor')
            ->will($this->returnValue($super));
        $runtime
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));
        $runtime
            ->expects($this->once())
            ->method('setLoopState')
            ->with(RuntimeModel::LOOP_STATE_FAILED);

        $runtime->fail($ex, $params);
    }

    /**
     *
     */
    public function testApiFail_HandlesErrorThrownByItself()
    {
        $ex1 = new Exception();
        $ex2 = new RejectionException();
        $params = [ 'param' => 'value' ];

        $super = $this->getMock(Supervisor::class, [], [], '', false);
        $super
            ->expects($this->twice())
            ->method('solve')
            ->will($this->returnValue(new PromiseRejected($ex2)));

        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('onTick')
            ->will($this->returnCallback(function($callable) {
                $callable();
            }));

        $runtime = $this->createModel([], [ 'getSupervisor', 'getLoop', 'setLoopState' ]);
        $runtime
            ->expects($this->once())
            ->method('getSupervisor')
            ->will($this->returnValue($super));
        $runtime
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));
        $runtime
            ->expects($this->once())
            ->method('setLoopState')
            ->with(RuntimeModel::LOOP_STATE_FAILED);

        $runtime->fail($ex1, $params);
    }

    /**
     *
     */
    public function testApiFail_CannotBeCalledMoreThanOnceInRow()
    {
        $ex = new Exception();
        $params = [];

        $super = $this->getMock(Supervisor::class, [], [], '', false);
        $loop  = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('onTick');
        $runtime = $this->createModel([], [ 'getSupervisor', 'getLoop', 'setLoopState' ]);
        $runtime
            ->expects($this->once())
            ->method('getSupervisor')
            ->will($this->returnValue($super));
        $runtime
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));
        $runtime
            ->expects($this->once())
            ->method('setLoopState');

        $runtime->fail($ex, $params);
        $runtime->fail($ex, $params);
    }

    /**
     *
     */
    public function testApiSucceed_ResumesLoop()
    {
        $runtime = $this->createModel([], [ 'setLoopState', 'startLoop' ]);
        $runtime
            ->expects($this->once())
            ->method('setLoopState')
            ->with(RuntimeModel::LOOP_STATE_STARTED);
        $runtime
            ->expects($this->once())
            ->method('startLoop');

        $runtime->succeed();
    }

    /**
     *
     */
    public function testProtectedApiSetLoopState_ReturnsImmediately_WhenNewStateDoesMatchCurrent()
    {
        $runtime = $this->createModel([], [ 'stopLoop' ]);
        $runtime
            ->expects($this->never())
            ->method('stopLoop');

        $state = RuntimeModel::LOOP_STATE_STOPPED;
        $this->setProtectedProperty($runtime, 'loopState', $state);

        $this->callProtectedMethod($runtime, 'setLoopState', [ $state ]);
    }

    /**
     *
     */
    public function testProtectedApiSetLoopState_StopsLoopAndChangesState_WhenNewStateDoesNotMatchCurrent()
    {
        $runtime = $this->createModel([], [ 'stopLoop' ]);
        $runtime
            ->expects($this->once())
            ->method('stopLoop');

        $old = RuntimeModel::LOOP_STATE_STOPPED;
        $new = RuntimeModel::LOOP_STATE_STARTED;
        $this->setProtectedProperty($runtime, 'loopState', $old);

        $this->callProtectedMethod($runtime, 'setLoopState', [ $new ]);

        $this->assertSame($new, $this->getProtectedProperty($runtime, 'loopState'));
    }

    /**
     *
     */
    public function testProtectedApiSetLoopState_ThrowsException_WhenNewStateIsInvalid()
    {
        $runtime = $this->createModel();

        $state = 'InvalidState';

        $this->setExpectedException(LogicException::class);
        $this->callProtectedMethod($runtime, 'setLoopState', [ $state ]);
    }

    /**
     *
     */
    public function testProtectedApiSetLoopState_ImportsDataFromBackupLoop_WhenSwitchedStateFromFailedToStarted()
    {
        $back = $this->getMock(Loop::class, [], [], '', false);
        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('import')
            ->with($back);

        $runtime = $this->createModel([], [ 'stopLoop' ]);
        $runtime
            ->expects($this->once())
            ->method('stopLoop');

        $this->setProtectedProperty($runtime, 'loop', $loop);
        $this->setProtectedProperty($runtime, 'loopBackup', $back);

        $old = RuntimeModel::LOOP_STATE_FAILED;
        $new = RuntimeModel::LOOP_STATE_STARTED;

        $this->setProtectedProperty($runtime, 'loopState', $old);
        $this->callProtectedMethod($runtime, 'setLoopState', [ $new ]);
    }

    /**
     *
     */
    public function testProtectedApiSetLoopState_ExportsDataFromBackupLoop_WhenSwitchedStateFromStartedToFailed()
    {
        $back = $this->getMock(Loop::class, [], [], '', false);
        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('export')
            ->with($back)
            ->will($this->returnSelf());
        $loop
            ->expects($this->once())
            ->method('erase');

        $runtime = $this->createModel([], [ 'stopLoop' ]);
        $runtime
            ->expects($this->once())
            ->method('stopLoop');

        $this->setProtectedProperty($runtime, 'loop', $loop);
        $this->setProtectedProperty($runtime, 'loopBackup', $back);

        $old = RuntimeModel::LOOP_STATE_STARTED;
        $new = RuntimeModel::LOOP_STATE_FAILED;

        $this->setProtectedProperty($runtime, 'loopState', $old);
        $this->callProtectedMethod($runtime, 'setLoopState', [ $new ]);
    }

    /**
     *
     */
    public function testProtectedApiGetLoopState_ReturnsLoopState()
    {
        $runtime = $this->createModel();
        $this->setProtectedProperty($runtime, 'loopState', $state = 'state');
        $result = $this->callProtectedMethod($runtime, 'getLoopState');

        $this->assertSame($state, $result);
    }

    /**
     *
     */
    public function testProtectedApiIsLoopState_ReturnsFalse_WhenLoopStateDoesNotMatch()
    {
        $runtime = $this->createModel();
        $this->setProtectedProperty($runtime, 'loopState', $state = 'state');

        $this->assertFalse($this->callProtectedMethod($runtime, 'isLoopState', [ 'other' ]));
    }

    /**
     *
     */
    public function testProtectedApiIsLoopState_ReturnsTrue_WhenLoopStateDoesMatch()
    {
        $runtime = $this->createModel();
        $this->setProtectedProperty($runtime, 'loopState', $state = 'state');

        $this->assertTrue($this->callProtectedMethod($runtime, 'isLoopState', [ $state ]));
    }

    /**
     *
     */
    public function testProtectedApiStartLoop_DoesNothing_WhenNextStateIsStopped()
    {
        $runtime = $this->createModel([], [ 'getLoop' ]);
        $runtime
            ->expects($this->never())
            ->method('getLoop');

        $this->callProtectedMethod($runtime, 'startLoop');
    }

    /**
     *
     */
    public function testProtectedApiStartLoop_StartsLoop_WhenNeeded()
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('start');

        $runtime = $this->createModel([], [ 'getLoop' ]);
        $runtime
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));

        $next = RuntimeModel::LOOP_STATE_STARTED;
        $this->setProtectedProperty($runtime, 'loopNextState', [ $next ]);

        $this->callProtectedMethod($runtime, 'startLoop');
    }

    /**
     *
     */
    public function testProtectedApiStartLoop_RestartsLoop_WhenNeeded()
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->twice())
            ->method('start');

        $cnt = 0;
        $runtime = $this->createModel([], [ 'getLoop' ]);
        $runtime
            ->expects($this->twice())
            ->method('getLoop')
            ->will($this->returnCallback(function() use(&$cnt, $loop, $runtime) {
                $cnt++;
                $next = ($cnt % 2 === 1) ? RuntimeModel::LOOP_STATE_STARTED : RuntimeModel::LOOP_STATE_STOPPED;
                $this->setProtectedProperty($runtime, 'loopNextState', $next);

                return $loop;
            }));

        $next = RuntimeModel::LOOP_STATE_STARTED;
        $this->setProtectedProperty($runtime, 'loopNextState', [ $next ]);

        $this->callProtectedMethod($runtime, 'startLoop');
    }

    /**
     *
     */
    public function testProtectedApiStartLoop_CallsFailMethod_WhenLoopThrowsException()
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('start')
            ->will($this->throwException($ex = new Exception));

        $runtime = $this->createModel([], [ 'getLoop', 'fail' ]);
        $runtime
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));
        $runtime
            ->expects($this->once())
            ->method('fail')
            ->with($ex);

        $next = RuntimeModel::LOOP_STATE_STARTED;
        $this->setProtectedProperty($runtime, 'loopNextState', [ $next ]);

        $this->callProtectedMethod($runtime, 'startLoop');
    }

    /**
     *
     */
    public function testProtectedApiStopLoop_StopsLoop()
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('stop');

        $runtime = $this->createModel([], [ 'getLoop' ]);
        $runtime
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));

        $this->callProtectedMethod($runtime, 'stopLoop');
    }

    /**
     * @return int[]
     */
    public function getStates()
    {
        return [
            Runtime::STATE_CREATED,
            Runtime::STATE_DESTROYED,
            Runtime::STATE_STARTED,
            Runtime::STATE_STOPPED
        ];
    }

    /**
     * @param string[] $params
     * @param string[]|null $methods
     * @return RuntimeModel|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createModel($params = [], $methods = null)
    {
        $params[0] = isset($params[0]) ? $params[0] : 'parent';
        $params[1] = isset($params[1]) ? $params[1] : 'alias';
        $params[2] = isset($params[2]) ? $params[2] : 'class';

        return $this->getMock(RuntimeModel::class, $methods, $params);
    }
}
