<?php

namespace Kraken\_Unit\Runtime;

use Kraken\_Unit\Runtime\_Mock\RuntimeManagerMock;
use Kraken\Channel\ChannelInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\Container\ThreadManagerInterface;
use Kraken\Runtime\RuntimeManager;
use Kraken\Runtime\RuntimeManagerInterface;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;

class RuntimeManagerTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $manager = $this->createRuntimeManager();

        $this->assertInstanceOf(RuntimeManager::class, $manager);
        $this->assertInstanceOf(RuntimeManagerInterface::class, $manager);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $manager = $this->createRuntimeManager();
        unset($manager);
    }

    /**
     *
     */
    public function testApiExistsRuntime_ReturnsTrue_WhenProcessManagerReturnsTrue()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->will($this->returnValue(true));

        $thread = $manager->getThread();
        $thread
            ->expects($this->never())
            ->method('existsThread')
            ->will($this->returnValue(false));

        $this->assertTrue($manager->existsRuntime($alias));
    }

    /**
     *
     */
    public function testApiExistsRuntime_ReturnsTrue_WhenThreadManagerReturnsTrue()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->will($this->returnValue(false));

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(true));

        $this->assertTrue($manager->existsRuntime($alias));
    }

    /**
     *
     */
    public function testApiExistsRuntime_ReturnsFalse_WhenBothManagersReturnsFalse()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->will($this->returnValue(false));

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(false));

        $this->assertFalse($manager->existsRuntime($alias));
    }

    /**
     *
     */
    public function testApiDestroyRuntime_DestroysThread_WhenRuntimeIsThread()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $flags = 'flags';

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(true));
        $thread
            ->expects($this->once())
            ->method('destroyThread')
            ->with($alias, $flags)
            ->will($this->returnValue(new PromiseFulfilled()));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->destroyRuntime($alias, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyRuntime_DestroysProcess_WhenRuntimeIsProcess()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $flags = 'flags';

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(false));

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->will($this->returnValue(true));
        $process
            ->expects($this->once())
            ->method('destroyProcess')
            ->with($alias, $flags)
            ->will($this->returnValue(new PromiseFulfilled()));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->destroyRuntime($alias, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyRuntime_RejectsPromise_WhenRuntimeDoesNotExist()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $flags = 'flags';

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(false));

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->will($this->returnValue(false));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ResourceUndefinedException::class));

        $manager
            ->destroyRuntime($alias, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartRuntime_StartsThread_WhenRuntimeIsThread()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(true));
        $thread
            ->expects($this->once())
            ->method('startThread')
            ->with($alias)
            ->will($this->returnValue(new PromiseFulfilled()));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->startRuntime($alias)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartRuntime_StartsProcess_WhenRuntimeIsProcess()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(false));

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->will($this->returnValue(true));
        $process
            ->expects($this->once())
            ->method('startProcess')
            ->with($alias)
            ->will($this->returnValue(new PromiseFulfilled()));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->startRuntime($alias)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartRuntime_RejectsPromise_WhenRuntimeDoesNotExist()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(false));

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->will($this->returnValue(false));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ResourceUndefinedException::class));

        $manager
            ->startRuntime($alias)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopRuntime_DestroysThread_WhenRuntimeIsThread()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(true));
        $thread
            ->expects($this->once())
            ->method('stopThread')
            ->with($alias)
            ->will($this->returnValue(new PromiseFulfilled()));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->stopRuntime($alias)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopRuntime_DestroysProcess_WhenRuntimeIsProcess()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(false));

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->will($this->returnValue(true));
        $process
            ->expects($this->once())
            ->method('stopProcess')
            ->with($alias)
            ->will($this->returnValue(new PromiseFulfilled()));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->stopRuntime($alias)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopRuntime_RejectsPromise_WhenRuntimeDoesNotExist()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->will($this->returnValue(false));

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->will($this->returnValue(false));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ResourceUndefinedException::class));

        $manager
            ->stopRuntime($alias)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyRuntimes_CallsDestroyRuntimeOnEachPassedRuntime()
    {
        $expected = [ $alias1 = 'alias1', $alias2 = 'alias2' ];
        $aliases  = [];
        $flags = 'flags';

        $manager = $this->createRuntimeManager([ 'destroyRuntime' ]);
        $manager
            ->expects($this->twice())
            ->method('destroyRuntime')
            ->with($this->isType('string'), $flags)
            ->will($this->returnCallback(function($alias, $flags) use(&$aliases) {
                $aliases[] = $alias;
            }));

        $manager->destroyRuntimes($expected, $flags);

        $this->assertSame($expected, $aliases);
    }

    /**
     *
     */
    public function testApiStartRuntimes_CallsStartRuntimeOnEachPassedRuntime()
    {
        $expected = [ $alias1 = 'alias1', $alias2 = 'alias2' ];
        $aliases  = [];

        $manager = $this->createRuntimeManager([ 'startRuntime' ]);
        $manager
            ->expects($this->twice())
            ->method('startRuntime')
            ->with($this->isType('string'))
            ->will($this->returnCallback(function($alias) use(&$aliases) {
                $aliases[] = $alias;
            }));

        $manager->startRuntimes($expected);

        $this->assertSame($expected, $aliases);
    }

    /**
     *
     */
    public function testApiStopRuntimes_CallsStopRuntimeOnEachPassedRuntime()
    {
        $expected = [ $alias1 = 'alias1', $alias2 = 'alias2' ];
        $aliases  = [];

        $manager = $this->createRuntimeManager([ 'stopRuntime' ]);
        $manager
            ->expects($this->twice())
            ->method('stopRuntime')
            ->with($this->isType('string'))
            ->will($this->returnCallback(function($alias) use(&$aliases) {
                $aliases[] = $alias;
            }));

        $manager->stopRuntimes($expected);

        $this->assertSame($expected, $aliases);
    }

    /**
     *
     */
    public function testApiGetRuntimes_ReturnsRuntimes()
    {
        $threads   = new PromiseFulfilled([ 'T1', 'T2' ]);
        $processes = new PromiseFulfilled([ 'P1', 'P2' ]);
        $expected  = array_merge([ 'T1', 'T2' ], [ 'P1', 'P2' ]);

        $manager = $this->createRuntimeManager([ 'getThreads', 'getProcesses' ]);
        $manager
            ->expects($this->once())
            ->method('getThreads')
            ->will($this->returnValue($threads));
        $manager
            ->expects($this->once())
            ->method('getProcesses')
            ->will($this->returnValue($processes));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($expected);

        $manager
            ->getRuntimes()
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiFlushRuntimes_FlushesRuntimes()
    {
        $manager = $this->createRuntimeManager([ 'flushThreads', 'flushProcesses' ]);
        $manager
            ->expects($this->once())
            ->method('flushThreads')
            ->will($this->returnValue(new PromiseFulfilled()));
        $manager
            ->expects($this->once())
            ->method('flushProcesses')
            ->will($this->returnValue(new PromiseFulfilled()));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->flushRuntimes()
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiExistsProcess_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('existsProcess')
            ->with($alias)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->existsProcess($alias));
    }

    /**
     *
     */
    public function testApiCreateProcess_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $flags = 'flags';
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('createProcess')
            ->with($alias, $flags)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->createProcess($alias, $flags));
    }

    /**
     *
     */
    public function testApiDestroyProcess_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('destroyProcess')
            ->with($alias)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->destroyProcess($alias));
    }

    /**
     *
     */
    public function testApiStartProcess_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('startProcess')
            ->with($alias)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->startProcess($alias));
    }

    /**
     *
     */
    public function testApiStopProcess_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('stopProcess')
            ->with($alias)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->stopProcess($alias));
    }


    /**
     *
     */
    public function testApiCreateProcesses_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $aliases = [ 'alias1', 'alias2' ];
        $flags = 'flags';
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('createProcesses')
            ->with($aliases, $flags)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->createProcesses($aliases, $flags));
    }

    /**
     *
     */
    public function testApiDestroyProcesses_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $aliases = [ 'alias1', 'alias2' ];
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('destroyProcesses')
            ->with($aliases)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->destroyProcesses($aliases));
    }

    /**
     *
     */
    public function testApiStartProcesses_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $aliases = [ 'alias1', 'alias2' ];
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('startProcesses')
            ->with($aliases)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->startProcesses($aliases));
    }

    /**
     *
     */
    public function testApiStopProcesses_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $aliases = [ 'alias1', 'alias2' ];
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('stopProcesses')
            ->with($aliases)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->stopProcesses($aliases));
    }

    /**
     *
     */
    public function testApiGetProcesses_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('getProcesses')
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->getProcesses());
    }

    /**
     *
     */
    public function testApiFlushProcesses_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $flags = 'flags';
        $promise = new Promise();

        $process = $manager->getProcess();
        $process
            ->expects($this->once())
            ->method('flushProcesses')
            ->with($flags)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->flushProcesses($flags));
    }

    /**
     *
     */
    public function testApiExistsThread_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('existsThread')
            ->with($alias)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->existsThread($alias));
    }

    /**
     *
     */
    public function testApiCreateThread_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $flags = 'flags';
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('createThread')
            ->with($alias, $flags)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->createThread($alias, $flags));
    }

    /**
     *
     */
    public function testApiDestroyThread_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('destroyThread')
            ->with($alias)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->destroyThread($alias));
    }

    /**
     *
     */
    public function testApiStartThread_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('startThread')
            ->with($alias)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->startThread($alias));
    }

    /**
     *
     */
    public function testApiStopThread_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $alias = 'alias';
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('stopThread')
            ->with($alias)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->stopThread($alias));
    }


    /**
     *
     */
    public function testApiCreateThreades_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $aliases = [ 'alias1', 'alias2' ];
        $flags = 'flags';
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('createThreads')
            ->with($aliases, $flags)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->createThreads($aliases, $flags));
    }

    /**
     *
     */
    public function testApiDestroyThreades_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $aliases = [ 'alias1', 'alias2' ];
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('destroyThreads')
            ->with($aliases)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->destroyThreads($aliases));
    }

    /**
     *
     */
    public function testApiStartThreades_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $aliases = [ 'alias1', 'alias2' ];
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('startThreads')
            ->with($aliases)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->startThreads($aliases));
    }

    /**
     *
     */
    public function testApiStopThreades_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $aliases = [ 'alias1', 'alias2' ];
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('stopThreads')
            ->with($aliases)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->stopThreads($aliases));
    }

    /**
     *
     */
    public function testApiGetThreades_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('getThreads')
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->getThreads());
    }

    /**
     *
     */
    public function testApiFlushThreades_CallsModelMethod()
    {
        $manager = $this->createRuntimeManager();
        $flags = 'flags';
        $promise = new Promise();

        $thread = $manager->getThread();
        $thread
            ->expects($this->once())
            ->method('flushThreads')
            ->with($flags)
            ->will($this->returnValue($promise));

        $this->assertSame($promise, $manager->flushThreads($flags));
    }



    /**
     * @param string[] $methods
     * @return RuntimeManagerMock|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRuntimeManager($methods = [])
    {
        $channel = $this->getMock(ChannelInterface::class, [], [], '', false);
        $process = $this->getMock(ProcessManagerInterface::class, [], [], '', false);
        $thread  = $this->getMock(ThreadManagerInterface::class, [], [], '', false);

        $methods = array_merge([ 'getThread', 'getProcess' ], $methods);

        $manager =  $this->getMock(RuntimeManagerMock::class, $methods, [ $channel, $process, $thread ]);
        $manager
            ->expects($this->any())
            ->method('getThread')
            ->will($this->returnValue($thread));
        $manager
            ->expects($this->any())
            ->method('getProcess')
            ->will($this->returnValue($process));

        return $manager;
    }
}
