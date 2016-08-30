<?php

namespace Kraken\_Unit\Runtime\Command\Arch;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\Extra\Request;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Runtime\Command\Arch\ArchStatusCommand;

class ArchStatusCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ArchStatusCommand::class;

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
                return new PromiseFulfilled([ 'param' => 'value' ]);
            }));

        $command = $this->createCommand([], [ 'createRequest' ]);
        $command
            ->expects($this->atLeastOnce())
            ->method('createRequest')
            ->will($this->returnValue($mock));

        $channel = $this->createChannel();
        $runtime = $this->createRuntime();
        $manager = $this->createManager();
        $manager
            ->expects($this->atLeastOnce())
            ->method('getRuntimes')
            ->will($this->returnValue(new PromiseFulfilled([ 'alias1', 'alias2' ])));

        $result = $this->callProtectedMethod(
            $command, 'command', []
        );

        $expect = $this->createCallableMock();
        $expect
            ->expects($this->once())
            ->method('__invoke')
            ->with([
                'parent'   => 'parent',
                'alias'    => 'alias',
                'name'     => 'name',
                'state'    => 2,
                'children' => [
                    [ 'param' => 'value' ],
                    [ 'param' => 'value' ]
                ]
            ]);

        $result
            ->then($expect);
    }

    /**
     *
     */
    public function testProtectedApiCreateRequest_CreatesRequest()
    {
        $channel  = $this->getMock(ChannelBaseInterface::class, [], [], '', false);
        $channel
            ->expects($this->any())
            ->method('createProtocol')
            ->will($this->returnCallback(function($message) {
                return new ChannelProtocol('', '', '', '', $message);
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
