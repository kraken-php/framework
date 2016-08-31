<?php

namespace Kraken\_Unit\Runtime\Container\Manager;

use Kraken\Channel\ChannelInterface;
use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\Extra\Request;
use Kraken\Core\CoreInterface;
use Kraken\Core\EnvironmentInterface;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Promise\PromiseRejected;
use Kraken\Runtime\Container\Manager\ProcessManagerBase;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\RuntimeInterface;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Logic\ResourceOccupiedException;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Kraken\Util\System\SystemInterface;
use StdClass;

class ProcessManagerBaseTest extends TUnit
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
        $manager = $this->createProcessManager();

        $this->assertInstanceOf(ProcessManagerBase::class, $manager);
        $this->assertInstanceOf(ProcessManagerInterface::class, $manager);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $manager = $this->createProcessManager();
        unset($manager);
    }

    /**
     *
     */
    public function testApiExistsProcess_ReturnsFalse_WhenProcessDoesNotExist()
    {
        $manager = $this->createProcessManager();

        $this->assertFalse($manager->existsProcess('alias'));
    }

    /**
     *
     */
    public function testApiExistsProcess_ReturnsTrue_WhenProcessDoesExist()
    {
        $manager = $this->createProcessManager();
        $this->setProtectedProperty($manager, 'processes', [ 'alias' => 'object' ]);

        $this->assertTrue($manager->existsProcess('alias'));
    }

    /**
     *
     */
    public function testApiCreateProcess_RejectsPromise_WhenProcessDoesNotExistAndNameIsNull()
    {
        $manager = $this->createProcessManager();
        $alias = 'alias';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(InvalidArgumentException::class));

        $manager
            ->createProcess($alias, null)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiCreateProcess_RejectsPromise_WhenProcessDoesExistAndForceIsDefault()
    {
        $manager = $this->createProcessManager();
        $manager->allocateProcess($alias = 'alias', $object = 'object', 1);
        $name = 'name';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ResourceOccupiedException::class));

        $manager
            ->createProcess($alias, $name)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyProcess_ResolvesPromise_WhenProcessDoesNotExist()
    {
        $manager = $this->createProcessManager();
        $alias = 'alias';
        $name = 'name';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->destroyProcess($alias, $name)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyProcess_RejectsPromise_WhenProcessDoesExistAndForceFlagIsDestroyKeep()
    {
        $manager = $this->createProcessManager();
        $manager->allocateProcess($alias = 'alias', $object = 'object', 1);
        $flags = Runtime::DESTROY_KEEP;

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ResourceOccupiedException::class));

        $manager
            ->destroyProcess($alias, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartProcess_InvokesValidCommand()
    {
        $manager = $this->createProcessManager();
        $manager->startProcess($alias = 'alias');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('container:start', $command->getCommand());
        $this->assertSame([], $command->getParams());
    }

    /**
     *
     */
    public function testApiStopProcess_InvokesValidCommand()
    {
        $manager = $this->createProcessManager();
        $manager->stopProcess($alias = 'alias');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('container:stop', $command->getCommand());
        $this->assertSame([], $command->getParams());
    }

    /**
     *
     */
    public function testApiCreateProcesses_ResolvesPromise_WhenAllProcessesAreCreated()
    {
        $manager = $this->createProcessManager([ 'createProcess' ]);
        $manager
            ->expects($this->twice())
            ->method('createProcess')
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object1', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object2', 2);
        $aliases = [ $alias1, $alias2 ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->createProcesses($aliases, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiCreateProcesses_RejectsPromise_WhenAtLeastOneProcessCouldNotBeCreated()
    {
        $manager = $this->createProcessManager([ 'createProcess' ]);
        $manager
            ->expects($this->twice())
            ->method('createProcess')
            ->will($this->returnValue(new PromiseRejected()));

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object', 2);
        $aliases = [ $alias1, $alias2 ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->createProcesses($aliases, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyProcesses_ResolvesPromise_WhenAllProcessesAreDestroyed()
    {
        $manager = $this->createProcessManager([ 'destroyProcess' ]);
        $manager
            ->expects($this->twice())
            ->method('destroyProcess')
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object', 2);
        $aliases = [ $alias1, $alias2 ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->destroyProcesses($aliases, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyProcesses_RejectsPromise_WhenAtLeastOneProcessCouldNotBeDestroyed()
    {
        $manager = $this->createProcessManager([ 'destroyProcess' ]);
        $manager
            ->expects($this->twice())
            ->method('destroyProcess')
            ->will($this->returnValue(new PromiseRejected()));

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object', 2);
        $aliases = [ $alias1, $alias2 ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->destroyProcesses($aliases, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartProcesses_ResolvesPromise_WhenAllProcessesAreStarted()
    {
        $manager = $this->createProcessManager([ 'startProcess' ]);
        $manager
            ->expects($this->twice())
            ->method('startProcess')
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object', 2);
        $aliases = [ $alias1, $alias2 ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->startProcesses($aliases)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartProcesses_RejectsPromise_WhenAtLeastOneProcessCouldNotBeStarted()
    {
        $manager = $this->createProcessManager([ 'startProcess' ]);
        $manager
            ->expects($this->twice())
            ->method('startProcess')
            ->will($this->returnValue(new PromiseRejected()));

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object', 2);
        $aliases = [ $alias1, $alias2 ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->startProcesses($aliases)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopProcesses_ResolvesPromise_WhenAllProcessesAreStopped()
    {
        $manager = $this->createProcessManager([ 'stopProcess' ]);
        $manager
            ->expects($this->twice())
            ->method('stopProcess')
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object', 2);
        $aliases = [ $alias1, $alias2 ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->stopProcesses($aliases)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopProcesses_RejectsPromise_WhenAtLeastOneProcessCouldNotBeStopped()
    {
        $manager = $this->createProcessManager([ 'stopProcess' ]);
        $manager
            ->expects($this->twice())
            ->method('stopProcess')
            ->will($this->returnValue(new PromiseRejected()));

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object', 2);
        $aliases = [ $alias1, $alias2 ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->stopProcesses($aliases)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiGetProcesses_ReturnsFulfilledPromiseThatContainsListOfProcesses()
    {
        $manager = $this->createProcessManager();
        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object1', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object2', 2);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with([ $alias1, $alias2 ]);

        $manager
            ->getProcesses()
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiFlushProcesses_RejectsPromise_WhenFlagIsDestroyKeep()
    {
        $flags = Runtime::DESTROY_KEEP;
        $manager = $this->createProcessManager([ 'destroyProcess' ]);
        $manager
            ->expects($this->never())
            ->method('destroyProcess');

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object1', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object2', 2);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->flushProcesses($flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiFlushProcesses_FlushesProcesses_WhenFlagIsOtherThanDestroyKeep()
    {
        $flags = Runtime::DESTROY_FORCE;
        $manager = $this->createProcessManager([ 'destroyProcess' ]);
        $manager
            ->expects($this->twice())
            ->method('destroyProcess')
            ->with($this->isType('string'), $flags)
            ->will($this->returnValue(new PromiseFulfilled()));

        $manager->allocateProcess($alias1 = 'alias1', $object1 = 'object1', 1);
        $manager->allocateProcess($alias2 = 'alias2', $object2 = 'object2', 2);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->flushProcesses($flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testProtectedApiAllocateProcess_AllocatesProcessAndReturnsTrue()
    {
        $manager = $this->createProcessManager();
        $result  = $manager->allocateProcess($alias = 'alias', $object = 'object', 1);

        $this->assertTrue($result);
        $this->assertSame(
            [ $alias=>[ 'pid'=>1, 'name'=>'object', 'verified'=>true ] ],
            $this->getProtectedProperty($manager, 'processes')
        );
    }

    /**
     *
     */
    public function testProtectedApiFreeProcess_FreesProcessAndReturnsTrue()
    {
        $manager = $this->createProcessManager();
        $manager->allocateProcess($alias = 'alias', $object = 'object', 2);
        $result  = $manager->freeProcess($alias = 'alias');

        $this->assertTrue($result);
        $this->assertSame([], $this->getProtectedProperty($manager, 'processes'));
    }

    /**
     *
     */
    public function testProtectedApiCreateRequest_CreatesRequest()
    {
        $channel  = $this->getMock(ChannelInterface::class, [], [], '', false);
        $channel
            ->expects($this->any())
            ->method('createProtocol')
            ->will($this->returnCallback(function($message) {
                return new ChannelProtocol('', '', '', '', $message);
            }));
        $receiver = 'receiver';
        $command  = 'command';

        $manager = $this->getMock(ProcessManagerBase::class, null, [], '', false);

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
     * @param string[]|null $methods
     * @return ProcessManagerBase|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createProcessManager($methods = [])
    {
        $core = $this->getMock(CoreInterface::class, [], [], '', false);
        $core
            ->expects($this->any())
            ->method('getDataPath')
            ->will($this->returnValue(''));
        $core
            ->expects($this->any())
            ->method('getDataDir')
            ->will($this->returnValue(''));

        $runtime = $this->getMock(RuntimeInterface::class, [], [], '', false);
        $runtime
            ->expects($this->any())
            ->method('getCore')
            ->will($this->returnValue($core));

        $channel = $this->getMock(ChannelInterface::class, [], [], '', false);
        $env     = $this->getMock(EnvironmentInterface::class, [], [], '', false);
        $system  = $this->getMock(SystemInterface::class, [], [], '', false);
        $fs      = $this->getMock(FilesystemInterface::class, [], [], '', false);
        $fs
            ->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));
        $fs
            ->expects($this->any())
            ->method('write')
            ->will($this->returnValue(null));

        $methods = array_merge($methods, [ 'createRequest' ]);
        $manager = $this->getMock(ProcessManagerBase::class, $methods, [ $runtime, $channel, $env, $system, $fs ]);
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
