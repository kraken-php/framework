<?php

namespace Kraken\_Unit\Transfer\Socket;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Transfer\Socket\SocketConnection;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Test\TUnit;

class SocketConnectionTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $socket = $this->createSocket();
        $conn = $this->createSocketConnection($socket);

        $this->assertInstanceOf(SocketConnection::class, $conn);
        $this->assertInstanceOf(TransferConnectionInterface::class, $conn);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $socket = $this->createSocket();
        $conn = $this->createSocketConnection($socket);
        unset($conn);
    }

    /**
     *
     */
    public function testApiGetResourceId_ReturnsResourceId()
    {
        $resource = 50;
        $socket = $this->createSocket();
        $socket
            ->expects($this->once())
            ->method('getResourceId')
            ->will($this->returnValue($resource));

        $conn = $this->createSocketConnection($socket);

        $this->assertSame($resource, $conn->getResourceId());
    }

    /**
     *
     */
    public function testApiGetEndpoint_ReturnsEndpoint()
    {
        $endpoint = 'endpoint';
        $socket = $this->createSocket();
        $socket
            ->expects($this->once())
            ->method('getRemoteEndpoint')
            ->will($this->returnValue($endpoint));

        $conn = $this->createSocketConnection($socket);

        $this->assertSame($endpoint, $conn->getEndpoint());
    }

    /**
     *
     */
    public function testApiGetAddress_ReturnsAddress()
    {
        $address = 'address';;
        $socket = $this->createSocket();
        $socket
            ->expects($this->once())
            ->method('getRemoteAddress')
            ->will($this->returnValue($address));

        $conn = $this->createSocketConnection($socket);

        $this->assertSame($address, $conn->getAddress());
    }

    /**
     *
     */
    public function testApiGetHost_ReturnsHost()
    {
        $address = 'host:port';
        $socket = $this->createSocket();
        $socket
            ->expects($this->once())
            ->method('getRemoteAddress')
            ->will($this->returnValue($address));

        $conn = $this->createSocketConnection($socket);

        $this->assertSame('host', $conn->getHost());
    }

    /**
     *
     */
    public function testApiGetPort_ReturnsPort()
    {
        $address = 'host:port';
        $socket = $this->createSocket();
        $socket
            ->expects($this->once())
            ->method('getRemoteAddress')
            ->will($this->returnValue($address));

        $conn = $this->createSocketConnection($socket);

        $this->assertSame('port', $conn->getPort());
    }

    /**
     *
     */
    public function testApiSend_WritesData()
    {
        $data = 'data';
        $socket = $this->createSocket();
        $socket
            ->expects($this->once())
            ->method('write')
            ->with($data);

        $conn = $this->createSocketConnection($socket);
        $conn->send($data);
    }

    /**
     *
     */
    public function testApiClose_ClosesConnection()
    {
        $socket = $this->createSocket();
        $socket
            ->expects($this->once())
            ->method('close');

        $conn = $this->createSocketConnection($socket);
        $conn->close();
    }

    /**
     * @return SocketInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSocket()
    {
        return $this->getMock(SocketInterface::class, [], [], '', false);
    }

    /**
     * @param SocketInterface $socket
     * @return SocketConnection
     */
    public function createSocketConnection(SocketInterface $socket)
    {
        return new SocketConnection($socket);
    }
}
