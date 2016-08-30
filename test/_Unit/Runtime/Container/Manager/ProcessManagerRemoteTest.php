<?php

namespace Kraken\_Unit\Runtime\Container\Manager;

use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\Extra\Request;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Runtime\Container\Manager\ProcessManagerRemote;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\RuntimeInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Kraken\Test\TUnit;

class ProcessManagerRemoteTest extends TUnit
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

        $this->assertInstanceOf(ProcessManagerRemote::class, $manager);
        $this->assertInstanceOf(ProcessManagerInterface::class, $manager);
    }

    /**
     *
     */
    public function testApiConstructor_SetsPassedReceiver_WhenReceiverIsPassed()
    {
        $manager = $this->createProcessManager($receiver = 'otherReceiver');

        $this->assertSame($receiver, $this->getProtectedProperty($manager, 'receiver'));
    }

    /**
     *
     */
    public function testApiConstructor_SetsDefaultReceiver_WhenReceiverIsNotPassed()
    {
        $manager = $this->createProcessManager();

        $this->assertSame('parent', $this->getProtectedProperty($manager, 'receiver'));
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
    public function testApiExistsProcess_ReturnsFalse()
    {
        $manager = $this->createProcessManager();
        $alias = 'alias';

        $this->assertFalse($manager->existsProcess($alias));
    }

    /**
     *
     */
    public function testApiCreateProcess_InvokesValidCommand()
    {
        $manager = $this->createProcessManager();
        $manager->createProcess($alias = 'alias', $name = 'name', $flags = 'flags');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('process:create', $command->getCommand());
        $this->assertSame([ 'alias' => $alias, 'name' => $name, 'flags' => $flags ], $command->getParams());
    }

    /**
     *
     */
    public function testApiDestroyProcess_InvokesValidCommand()
    {
        $manager = $this->createProcessManager();
        $manager->destroyProcess($alias = 'alias', $flags = 'flags');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('process:destroy', $command->getCommand());
        $this->assertSame([ 'alias' => $alias, 'flags' => $flags ], $command->getParams());
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
        $this->assertSame('process:start', $command->getCommand());
        $this->assertSame([ 'alias' => $alias ], $command->getParams());
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
        $this->assertSame('process:stop', $command->getCommand());
        $this->assertSame([ 'alias' => $alias ], $command->getParams());
    }

    /**
     *
     */
    public function testApiCreateProcesses_InvokesValidCommand()
    {
        $manager = $this->createProcessManager();
        $manager->createProcesses($definitions = [ 'alias' => 'name' ], $flags = 'flags');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('processes:create', $command->getCommand());
        $this->assertSame([ 'definitions' => $definitions, 'flags' => $flags ], $command->getParams());
    }

    /**
     *
     */
    public function testApiDestroyProcesses_InvokesValidCommand()
    {
        $manager = $this->createProcessManager();
        $manager->destroyProcesses($aliases = [ 'alias' ], $flags = 'flags');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('processes:destroy', $command->getCommand());
        $this->assertSame([ 'aliases' => $aliases, 'flags' => $flags ], $command->getParams());
    }

    /**
     *
     */
    public function testApiStartProcesses_InvokesValidCommand()
    {
        $manager = $this->createProcessManager();
        $manager->startProcesses($aliases = [ 'alias' ]);
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('processes:start', $command->getCommand());
        $this->assertSame([ 'aliases' => $aliases ], $command->getParams());
    }

    /**
     *
     */
    public function testApiStopProcesses_InvokesValidCommand()
    {
        $manager = $this->createProcessManager();
        $manager->stopProcesses($aliases = [ 'alias' ]);
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('processes:stop', $command->getCommand());
        $this->assertSame([ 'aliases' => $aliases ], $command->getParams());
    }

    /**
     *
     */
    public function testApiGetProcesses_InvokesValidCommand()
    {
        $manager = $this->createProcessManager();
        $manager->getProcesses();
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('processes:get', $command->getCommand());
        $this->assertSame([], $command->getParams());
    }

    /**
     *
     */
    public function testApiFlushProcesses_RejectsPromise()
    {
        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager = $this->createProcessManager();
        $manager
            ->flushProcesses()
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testProtectedApiCreateRequest_CreatesRequest()
    {
        $runtime  = $this->getMock(RuntimeInterface::class, [], [], '', false);
        $channel  = $this->getMock(ChannelBaseInterface::class, [], [], '', false);
        $channel
            ->expects($this->any())
            ->method('createProtocol')
            ->will($this->returnCallback(function($message) {
                return new ChannelProtocol('', '', '', '', $message);
            }));
        $receiver = 'receiver';
        $command  = 'command';

        $manager = new ProcessManagerRemote($runtime, $channel, $receiver);

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
     * @param string|null $receiver
     * @return ProcessManagerRemote
     */
    public function createProcessManager($receiver = null)
    {
        $runtime = $this->getMock(RuntimeInterface::class, [], [], '', false);
        $runtime
            ->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue('parent'));

        $channel = $this->getMock(ChannelBaseInterface::class, [], [], '', false);

        $manager = $this->getMock(ProcessManagerRemote::class, [ 'createRequest' ], [ $runtime, $channel, $receiver ]);
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
