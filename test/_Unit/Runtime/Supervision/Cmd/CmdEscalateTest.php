<?php

namespace Kraken\_Unit\Runtime\Supervision;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Channel\Extra\Request;
use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\ChannelInterface;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Supervision\Cmd\CmdEscalate;
use Exception;
use stdClass;

class CmdEscalateTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = CmdEscalate::class;

    /**
     *
     */
    public function testApisolver_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];
        $result = new StdClass;

        $call = $this->getMock(Request::class, [], [], '', false);
        $call
            ->expects($this->once())
            ->method('call')
            ->will($this->returnValue($result));

        $solver  = $this->createSolver([], [ 'createRequest' ]);
        $solver
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                $this->isInstanceOf(ChannelInterface::class),
                'parent',
                $this->isInstanceOf(RuntimeCommand::class)
            )
            ->will($this->returnValue($call));

        $this->createChannel();

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $solver, 'solver', [ $ex, $params ]
            )
        );
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

        $cmd = $this->createSolver();

        $req = $this->callProtectedMethod($cmd, 'createRequest', [ $channel, $receiver, $command ]);

        $this->assertInstanceOf(Request::class, $req);
        $this->assertSame($channel,  $this->getProtectedProperty($req, 'channel'));
        $this->assertSame($receiver, $this->getProtectedProperty($req, 'name'));
        $this->assertSame($command,  $this->getProtectedProperty($req, 'message')->getMessage());
    }
}
