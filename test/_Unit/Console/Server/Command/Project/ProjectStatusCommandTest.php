<?php

namespace Kraken\_Unit\Console\Server\Command\Project;

use Kraken\_Unit\Console\Server\_T\TCommand;
use Kraken\Channel\Extra\Request;
use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\ChannelInterface;
use Kraken\Config\ConfigInterface;
use Kraken\Console\Server\Command\Project\ProjectStatusCommand;
use Dazzle\Promise\PromiseFulfilled;
use Kraken\Runtime\RuntimeCommand;

class ProjectStatusCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProjectStatusCommand::class;

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
        $channel = $this->createChannel();
        $config  = $this->createConfig([ 'main.alias' => 'main' ]);

        $command
            ->expects($this->atLeastOnce())
            ->method('createRequest')
            ->with($channel, 'main', $this->isInstanceOf(RuntimeCommand::class))
            ->will($this->returnValue($mock));

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

    /**
     *
     */
    public function testProtectedApiCreateConfig_CreatesConfig()
    {
        $cmd = $this->createCommand();

        $this->assertInstanceOf(ConfigInterface::class, $this->callProtectedMethod($cmd, 'createConfig'));
    }
}
