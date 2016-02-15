<?php

namespace Kraken\Test\Unit\Ipc\Socket;

use Kraken\Exception\Runtime\InstantiationException;
use Kraken\Ipc\Socket\SocketServer;
use Kraken\Loop\LoopInterface;
use Kraken\Test\Unit\TestCase;

class SocketServerTest extends TestCase
{
    public function testConstructor()
    {
        $socket = $this->createSocketServerMock();
    }

    public function testConstructor_ThrowsException_OnInvalidResource()
    {
        $this->setExpectedException(InstantiationException::class);
        $socket = $this->createSocketServerMock('invalid');
    }

    public function testConstructor_ThrowsException_OnOccupiedEndpoint()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $this->setExpectedException(InstantiationException::class);
        $socket = $this->createSocketServerMock();
    }

    public function testApiGetLocalEndpoint_ReturnsValidEndpoint()
    {
        $socket = $this->createSocketServerMock($this->tempSocketRemoteAddress());
        $this->assertRegExp('#^tcp://(([0-9]*?)\.){3}([0-9]*?):([0-9]*?)$#si', $socket->getLocalEndpoint());
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface $loop
     * @return SocketServer
     */
    protected function createSocketServerMock($resource = null, LoopInterface $loop = null)
    {
        return $this->createSocketServerInjection(
            is_null($resource) ? $this->tempSocketRemoteAddress() : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }

    /**
     * @param string|resource $endpointOrResource
     * @param LoopInterface $loop
     * @return SocketServer
     */
    protected function createSocketServerInjection($endpointOrResource, LoopInterface $loop)
    {
        return new SocketServer($endpointOrResource, $loop);
    }

    /**
     * @return string
     */
    private function tempSocketRemoteAddress()
    {
        return 'tcp://127.0.0.1:2080';
    }
}
