<?php

namespace Kraken\_Unit\Runtime\Container\Manager;

use Kraken\Channel\ChannelInterface;
use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\Extra\Request;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Promise\PromiseRejected;
use Kraken\Runtime\Container\Manager\ThreadManagerBase;
use Kraken\Runtime\Container\ThreadManagerInterface;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Logic\ResourceOccupiedException;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use StdClass;

class ThreadManagerBaseTest extends TUnit
{
    /**
     * @var RuntimeCommand
     */
    private $command;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $manager = $this->createThreadManager();

        $this->assertInstanceOf(ThreadManagerBase::class, $manager);
        $this->assertInstanceOf(ThreadManagerInterface::class, $manager);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $manager = $this->createThreadManager();
        unset($manager);
    }

    /**
     *
     */
    public function testApiExistsThread_ReturnsFalse_WhenThreadDoesNotExist()
    {
        $manager = $this->createThreadManager();

        $this->assertFalse($manager->existsThread('alias'));
    }

    /**
     *
     */
    public function testApiExistsThread_ReturnsTrue_WhenThreadDoesExist()
    {
        $manager = $this->createThreadManager();
        $this->setProtectedProperty($manager, 'threads', [ 'alias' => new StdClass ]);

        $this->assertTrue($manager->existsThread('alias'));
    }

    /**
     *
     */
    public function testApiCreateThread_RejectsPromise_WhenThreadDoesNotExistAndNameIsNull()
    {
        $manager = $this->createThreadManager();
        $alias = 'alias';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(InvalidArgumentException::class));

        $manager
            ->createThread($alias, null)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiCreateThread_RecreatesThreadWithForceSoft_WhenThreadDoesExistAndForceIsForceSoft()
    {
        $manager = $this->createThreadManager([ 'destroyThread' ]);
        $manager->allocateThread($alias = 'alias', $object = new StdClass);
        $name = 'name';
        $flags = Runtime::CREATE_FORCE_SOFT;

        $manager
            ->expects($this->once())
            ->method('destroyThread')
            ->with($alias, Runtime::DESTROY_FORCE_SOFT)
            ->will($this->returnCallback(function() {
                $mock = $this->getMock(PromiseFulfilled::class, [ 'then' ], [], '', false);
                $mock
                    ->expects($this->any())
                    ->method('then')
                    ->will($this->returnCallback(function($func) {
                        return new PromiseFulfilled();
                    }));

                return $mock;
            }));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->createThread($alias, $name, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiCreateThread_RecreatesThreadWithForceHard_WhenThreadDoesExistAndForceIsForceHard()
    {
        $manager = $this->createThreadManager([ 'destroyThread' ]);
        $manager->allocateThread($alias = 'alias', $object = new StdClass);
        $name = 'name';
        $flags = Runtime::CREATE_FORCE_HARD;

        $manager
            ->expects($this->once())
            ->method('destroyThread')
            ->with($alias, Runtime::DESTROY_FORCE_HARD)
            ->will($this->returnCallback(function() {
                $mock = $this->getMock(PromiseFulfilled::class, [ 'then' ], [], '', false);
                $mock
                    ->expects($this->any())
                    ->method('then')
                    ->will($this->returnCallback(function($func) {
                        return new PromiseFulfilled();
                    }));

                return $mock;
            }));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->createThread($alias, $name, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiCreateThread_RecreatesThreadWithForce_WhenThreadDoesExistAndForceIsForce()
    {
        $manager = $this->createThreadManager([ 'destroyThread' ]);
        $manager->allocateThread($alias = 'alias', $object = new StdClass);
        $name = 'name';
        $flags = Runtime::CREATE_FORCE;

        $manager
            ->expects($this->once())
            ->method('destroyThread')
            ->with($alias, Runtime::DESTROY_FORCE)
            ->will($this->returnCallback(function() {
                $mock = $this->getMock(PromiseFulfilled::class, [ 'then' ], [], '', false);
                $mock
                    ->expects($this->any())
                    ->method('then')
                    ->will($this->returnCallback(function($func) {
                        return new PromiseFulfilled();
                    }));

                return $mock;
            }));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke');

        $manager
            ->createThread($alias, $name, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiCreateThread_RejectsPromise_WhenThreadDoesExistAndForceIsDefault()
    {
        $manager = $this->createThreadManager();
        $manager->allocateThread($alias = 'alias', $object = new StdClass);
        $name = 'name';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ResourceOccupiedException::class));

        $manager
            ->createThread($alias, $name)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyThread_ResolvesPromise_WhenThreadDoesNotExist()
    {
        $manager = $this->createThreadManager();
        $alias = 'alias';
        $name = 'name';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->destroyThread($alias, $name)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyThread_RejectsPromise_WhenThreadDoesExistAndForceFlagIsDestroyKeep()
    {
        $manager = $this->createThreadManager();
        $manager->allocateThread($alias = 'alias', $object = new StdClass);
        $flags = Runtime::DESTROY_KEEP;

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ResourceOccupiedException::class));

        $manager
            ->destroyThread($alias, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartThread_InvokesValidCommand()
    {
        $manager = $this->createThreadManager();
        $manager->startThread($alias = 'alias');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('container:start', $command->getCommand());
        $this->assertSame([], $command->getParams());
    }

    /**
     *
     */
    public function testApiStopThread_InvokesValidCommand()
    {
        $manager = $this->createThreadManager();
        $manager->stopThread($alias = 'alias');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('container:stop', $command->getCommand());
        $this->assertSame([], $command->getParams());
    }

    /**
     *
     */
    public function testApiCreateThreads_ResolvesPromise_WhenAllThreadsAreCreated()
    {
        $manager = $this->createThreadManager([ 'createThread' ]);
        $manager
            ->expects($this->twice())
            ->method('createThread')
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);
        $aliases = [ $alias1, $alias2 ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->createThreads($aliases, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiCreateThreads_RejectsPromise_WhenAtLeastOneThreadCouldNotBeCreated()
    {
        $manager = $this->createThreadManager([ 'createThread' ]);
        $manager
            ->expects($this->twice())
            ->method('createThread')
            ->will($this->returnValue(new PromiseRejected()));

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);
        $aliases = [ $alias1, $alias2 ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->createThreads($aliases, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyThreads_ResolvesPromise_WhenAllThreadsAreDestroyed()
    {
        $manager = $this->createThreadManager([ 'destroyThread' ]);
        $manager
            ->expects($this->twice())
            ->method('destroyThread')
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);
        $aliases = [ $alias1, $alias2 ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->destroyThreads($aliases, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyThreads_RejectsPromise_WhenAtLeastOneThreadCouldNotBeDestroyed()
    {
        $manager = $this->createThreadManager([ 'destroyThread' ]);
        $manager
            ->expects($this->twice())
            ->method('destroyThread')
            ->will($this->returnValue(new PromiseRejected()));

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);
        $aliases = [ $alias1, $alias2 ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->destroyThreads($aliases, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartThreads_ResolvesPromise_WhenAllThreadsAreStarted()
    {
        $manager = $this->createThreadManager([ 'startThread' ]);
        $manager
            ->expects($this->twice())
            ->method('startThread')
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);
        $aliases = [ $alias1, $alias2 ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->startThreads($aliases)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartThreads_RejectsPromise_WhenAtLeastOneThreadCouldNotBeStarted()
    {
        $manager = $this->createThreadManager([ 'startThread' ]);
        $manager
            ->expects($this->twice())
            ->method('startThread')
            ->will($this->returnValue(new PromiseRejected()));

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);
        $aliases = [ $alias1, $alias2 ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->startThreads($aliases)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopThreads_ResolvesPromise_WhenAllThreadsAreStopped()
    {
        $manager = $this->createThreadManager([ 'stopThread' ]);
        $manager
            ->expects($this->twice())
            ->method('stopThread')
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);
        $aliases = [ $alias1, $alias2 ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->stopThreads($aliases)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopThreads_RejectsPromise_WhenAtLeastOneThreadCouldNotBeStopped()
    {
        $manager = $this->createThreadManager([ 'stopThread' ]);
        $manager
            ->expects($this->twice())
            ->method('stopThread')
            ->will($this->returnValue(new PromiseRejected()));

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);
        $aliases = [ $alias1, $alias2 ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->stopThreads($aliases)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiGetThreads_ReturnsFulfilledPromiseThatContainsListOfThreads()
    {
        $manager = $this->createThreadManager();
        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with([ $alias1, $alias2 ]);

        $manager
            ->getThreads()
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiFlushThreads_RejectsPromise_WhenFlagIsDestroyKeep()
    {
        $flags = Runtime::DESTROY_KEEP;
        $manager = $this->createThreadManager([ 'destroyThread' ]);
        $manager
            ->expects($this->never())
            ->method('destroyThread');

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->flushThreads($flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiFlushThreads_FlushesThreads_WhenFlagIsOtherThanDestroyKeep()
    {
        $flags = Runtime::DESTROY_FORCE;
        $manager = $this->createThreadManager([ 'destroyThread' ]);
        $manager
            ->expects($this->twice())
            ->method('destroyThread')
            ->with($this->isType('string'), $flags)
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateThread($alias1 = 'alias1', $object1 = new StdClass);
        $manager->allocateThread($alias2 = 'alias2', $object2 = new StdClass);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->flushThreads($flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testProtectedApiAllocateThread_AllocatesThreadAndReturnsTrue()
    {
        $manager = $this->createThreadManager();
        $result  = $manager->allocateThread($alias = 'alias', $object = new StdClass);

        $this->assertTrue($result);
        $this->assertSame([ $alias=>$object ], $this->getProtectedProperty($manager, 'threads'));
    }

    /**
     *
     */
    public function testProtectedApiFreeThread_FreesThreadAndReturnsTrue()
    {
        $manager = $this->createThreadManager();
        $manager->allocateThread($alias = 'alias', $object = new StdClass);
        $result  = $manager->freeThread($alias = 'alias');

        $this->assertTrue($result);
        $this->assertSame([], $this->getProtectedProperty($manager, 'threads'));
    }

    /**
     *
     */
    public function testProtectedApiCreateRequest_CreatesRequest()
    {
        $runtime  = $this->getMock(RuntimeContainerInterface::class, [], [], '', false);
        $channel  = $this->getMock(ChannelInterface::class, [], [], '', false);
        $channel
            ->expects($this->any())
            ->method('createProtocol')
            ->will($this->returnCallback(function($message) {
                return new ChannelProtocol('', '', '', '', $message);
            }));
        $receiver = 'receiver';
        $command  = 'command';

        $manager = new ThreadManagerBase($runtime, $channel, $receiver);

        $req = $this->callProtectedMethod($manager, 'createRequest', [ $channel, $receiver, $command ]);

        $this->assertInstanceOf(Request::class, $req);
        $this->assertSame($channel,  $this->getProtectedProperty($req, 'channel'));
        $this->assertSame($receiver, $this->getProtectedProperty($req, 'name'));
        $this->assertSame($command,  $this->getProtectedProperty($req, 'message')->getMessage());
    }

    /**
     * @return RuntimeCommand
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string[] $methods
     * @return ThreadManagerBase|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createThreadManager($methods = [])
    {
        $runtime = $this->getMock(RuntimeContainerInterface::class, [], [], '', false);
        $channel = $this->getMock(ChannelInterface::class, [], [], '', false);

        $methods = array_merge($methods, [ 'createRequest' ]);
        $manager = $this->getMock(ThreadManagerBase::class, $methods, [ $runtime, $channel, [] ]);
        $manager
            ->expects($this->any())
            ->method('createRequest')
            ->will($this->returnCallback(function($channel, $receiver, $command) {
                $this->command = $command;

                $mock = $this->getMock(RuntimeCommand::class, [ 'call' ], [ $channel, $receiver, $command ]);
                $mock
                    ->expects($this->once())
                    ->method('call')
                    ->will($this->returnValue(new PromiseFulfilled()));

                return $mock;
            }));

        return $manager;
    }
}
