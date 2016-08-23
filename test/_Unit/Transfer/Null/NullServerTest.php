<?php

namespace Kraken\_Unit\Transfer\Null;

use Exception;
use Kraken\Transfer\Null\NullServer;
use Kraken\Test\TUnit;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\TransferMessageInterface;

class NullServerTest extends TUnit
{
    /**
     *
     */
    public function testApiHandleConnect_DoesNothing()
    {
        $server = $this->createServer();
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);

        $server->handleConnect($conn);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_DoesNothing()
    {
        $server = $this->createServer();
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);

        $server->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleMessage_DoesNothing()
    {
        $server = $this->createServer();
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $mssg = $this->getMock(TransferMessageInterface::class, [], [], '', false);

        $server->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleError_DoesNothing()
    {
        $server = $this->createServer();
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $ex = new Exception();

        $server->handleError($conn, $ex);
    }

    /**
     * @return NullServer
     */
    public function createServer()
    {
        return new NullServer();
    }
}
