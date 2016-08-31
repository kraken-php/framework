<?php

namespace Kraken\_Unit\Runtime\Container\Manager;

use Kraken\Channel\ChannelInterface;
use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\Extra\Request;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Runtime\Container\Manager\ThreadManagerRemote;
use Kraken\Runtime\Container\ThreadManagerInterface;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ThreadManagerRemoteTest extends TUnit
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

        $this->assertInstanceOf(ThreadManagerRemote::class, $manager);
        $this->assertInstanceOf(ThreadManagerInterface::class, $manager);
    }

    /**
     *
     */
    public function testApiConstructor_SetsPassedReceiver_WhenReceiverIsPassed()
    {
        $manager = $this->createThreadManager($receiver = 'otherReceiver');

        $this->assertSame($receiver, $this->getProtectedProperty($manager, 'receiver'));
    }

    /**
     *
     */
    public function testApiConstructor_SetsDefaultReceiver_WhenReceiverIsNotPassed()
    {
        $manager = $this->createThreadManager();

        $this->assertSame('parent', $this->getProtectedProperty($manager, 'receiver'));
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
    public function testApiExistsThread_ReturnsFalse()
    {
        $manager = $this->createThreadManager();
        $alias = 'alias';

        $this->assertFalse($manager->existsThread($alias));
    }

    /**
     *
     */
    public function testApiCreateThread_InvokesValidCommand()
    {
        $manager = $this->createThreadManager();
        $manager->createThread($alias = 'alias', $name = 'name', $flags = 'flags');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('thread:create', $command->getCommand());
        $this->assertSame([ 'alias' => $alias, 'name' => $name, 'flags' => $flags ], $command->getParams());
    }

    /**
     *
     */
    public function testApiDestroyThread_InvokesValidCommand()
    {
        $manager = $this->createThreadManager();
        $manager->destroyThread($alias = 'alias', $flags = 'flags');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('thread:destroy', $command->getCommand());
        $this->assertSame([ 'alias' => $alias, 'flags' => $flags ], $command->getParams());
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
        $this->assertSame('thread:start', $command->getCommand());
        $this->assertSame([ 'alias' => $alias ], $command->getParams());
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
        $this->assertSame('thread:stop', $command->getCommand());
        $this->assertSame([ 'alias' => $alias ], $command->getParams());
    }

    /**
     *
     */
    public function testApiCreateThreads_InvokesValidCommand()
    {
        $manager = $this->createThreadManager();
        $manager->createThreads($definitions = [ 'alias' => 'name' ], $flags = 'flags');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('threads:create', $command->getCommand());
        $this->assertSame([ 'definitions' => $definitions, 'flags' => $flags ], $command->getParams());
    }

    /**
     *
     */
    public function testApiDestroyThreads_InvokesValidCommand()
    {
        $manager = $this->createThreadManager();
        $manager->destroyThreads($aliases = [ 'alias' ], $flags = 'flags');
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('threads:destroy', $command->getCommand());
        $this->assertSame([ 'aliases' => $aliases, 'flags' => $flags ], $command->getParams());
    }

    /**
     *
     */
    public function testApiStartThreads_InvokesValidCommand()
    {
        $manager = $this->createThreadManager();
        $manager->startThreads($aliases = [ 'alias' ]);
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('threads:start', $command->getCommand());
        $this->assertSame([ 'aliases' => $aliases ], $command->getParams());
    }

    /**
     *
     */
    public function testApiStopThreads_InvokesValidCommand()
    {
        $manager = $this->createThreadManager();
        $manager->stopThreads($aliases = [ 'alias' ]);
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('threads:stop', $command->getCommand());
        $this->assertSame([ 'aliases' => $aliases ], $command->getParams());
    }

    /**
     *
     */
    public function testApiGetThreads_InvokesValidCommand()
    {
        $manager = $this->createThreadManager();
        $manager->getThreads();
        $command = $this->getCommand();

        $this->assertInstanceOf(RuntimeCommand::class, $command);
        $this->assertSame('threads:get', $command->getCommand());
        $this->assertSame([], $command->getParams());
    }

    /**
     *
     */
    public function testApiFlushThreads_RejectsPromise()
    {
        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager = $this->createThreadManager();
        $manager
            ->flushThreads()
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

        $manager = new ThreadManagerRemote($runtime, $channel, $receiver);

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
     * @return ThreadManagerRemote
     */
    public function createThreadManager($receiver = null)
    {
        $runtime = $this->getMock(RuntimeContainerInterface::class, [], [], '', false);
        $runtime
            ->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue('parent'));

        $channel = $this->getMock(ChannelInterface::class, [], [], '', false);

        $manager = $this->getMock(ThreadManagerRemote::class, [ 'createRequest' ], [ $runtime, $channel, $receiver ]);
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
