<?php

namespace Kraken\_Unit\Network\Socket;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Network\NetworkConnection;
use Kraken\Network\NetworkConnectionInterface;
use Kraken\Test\TUnit;

class NetworkConnectionTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $socket = $this->createSocket();
        $conn = $this->createNetworkConnection($socket);

        $this->assertInstanceOf(NetworkConnection::class, $conn);
        $this->assertInstanceOf(NetworkConnectionInterface::class, $conn);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $socket = $this->createSocket();
        $conn = $this->createNetworkConnection($socket);
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

        $conn = $this->createNetworkConnection($socket);

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

        $conn = $this->createNetworkConnection($socket);

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

        $conn = $this->createNetworkConnection($socket);

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

        $conn = $this->createNetworkConnection($socket);

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

        $conn = $this->createNetworkConnection($socket);

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

        $conn = $this->createNetworkConnection($socket);
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

        $conn = $this->createNetworkConnection($socket);
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
     * @return NetworkConnection
     */
    public function createNetworkConnection(SocketInterface $socket)
    {
        return new NetworkConnection($socket);
    }
}
