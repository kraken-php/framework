<?php

namespace Kraken\_Unit\Runtime\Command\Arch;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Channel\Extra\Request;
use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\ChannelInterface;
use Dazzle\Promise\PromiseFulfilled;
use Kraken\Runtime\Command\Arch\ArchStartCommand;

class ArchStartCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ArchStartCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $mock = $this->getMock(Request::class, [], [], '', false);
        $mock
            ->expects($this->any())
            ->method('call')
            ->will($this->returnCallback(function() {
                return new PromiseFulfilled('ok');
            }));

        $command = $this->createCommand([], [ 'createRequest' ]);
        $command
            ->expects($this->atLeastOnce())
            ->method('createRequest')
            ->will($this->returnValue($mock));

        $channel = $this->createChannel();

        $runtime = $this->createRuntime([ 'start' ]);
        $runtime
            ->expects($this->once())
            ->method('start')
            ->will($this->returnValue(new PromiseFulfilled('')));

        $manager = $this->createManager();
        $manager
            ->expects($this->atLeastOnce())
            ->method('getRuntimes')
            ->will($this->returnValue(new PromiseFulfilled([ 'alias1' ])));

        $result = $this->callProtectedMethod(
            $command, 'command', []
        );

        $expect = $this->createCallableMock();
        $expect
            ->expects($this->once())
            ->method('__invoke');

        $result
            ->then($expect);
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
                return new Protocol('', '', '', '', $message);
            }));
        $receiver = 'receiver';
        $command  = 'command';

        $cmd = $this->createCommand();

        $req = $this->callProtectedMethod($cmd, 'createRequest', [ $channel, $receiver, $command ]);

        $this->assertInstanceOf(Request::class, $req);
        $this->assertSame($channel,  $this->getProtectedProperty($req, 'channel'));
        $this->assertSame($receiver, $this->getProtectedProperty($req, 'name'));
        $this->assertSame($command,  $this->getProtectedProperty($req, 'message')->getMessage());
    }
}
