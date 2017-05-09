<?php

namespace Kraken\_Unit\Ipc\Socket;

use Kraken\Ipc\Socket\Socket;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use Kraken\Loop\LoopInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Test\TUnit;

class SocketTest extends TUnit
{
    /**
     *
     */
    public function tearDown()
    {
        $path = str_replace('unix://', '', $this->tempSocketAddress());

        if (file_exists($path))
        {
            unlink($path);
        }

        parent::tearDown();
    }

    public function testApiConstructor_CreatesInstance()
    {
        $server = stream_socket_server($this->tempSocketAddress());
        $socket = $this->createSocketMock();

        $this->assertInstanceOf(Socket::class, $socket);
        $this->assertInstanceOf(SocketInterface::class, $socket);
    }

    /**
     *
     */
    public function testApiConstructor_ThrowsException_WhenNoServerExists()
    {
        $this->setExpectedException(InstantiationException::class);
        $this->createSocketMock();
    }

    /**
     *
     */
    public function testApiConstructor_ThrowsException_OnInvalidResource()
    {
        $this->setExpectedException(InstantiationException::class);
        $this->createSocketMock('invalid');
    }

    /**
     *
     */
    public function testDestructor_DoesNotThrowException()
    {
        $server = stream_socket_server($this->tempSocketAddress());
        $socket = $this->createSocketMock();

        unset($socket);
    }

    /**
     *
     */
    public function testApiStop_StopsSocket()
    {
        $server = stream_socket_server($this->tempSocketAddress());
        $socket = $this->createSocketMock();

        $this->assertTrue($socket->isOpen());
        $socket->stop();
        $this->assertFalse($socket->isOpen());
    }

    /**
     *
     */
    public function testApiGetLocalEndpoint_ReturnsEndpoint()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress(),$errno,$errstr,STREAM_SERVER_BIND);
        $socket = $this->createSocketMock($this->tempSocketRemoteAddress());

        $this->assertRegExp('#^tcp://(([0-9]*?)\.){3}([0-9]*?):([0-9]*?)$#si', $socket->getLocalEndpoint());
    }

    /**
     *
     */
    public function testApiGetRemoteEndpoint_ReturnsEndpoint()
    {
        $remote = $this->tempSocketRemoteAddress();
        $server = stream_socket_server($remote,$errno,$errstr);
        $socket = $this->createSocketMock($remote);
        $this->assertEquals($remote, $socket->getRemoteEndpoint());
    }

    /**
     *
     */
    public function testApiGetLocalAddress_ReturnsAddress()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $socket = $this->createSocketMock($this->tempSocketRemoteAddress());
        $pattern = '#^(([0-9]*?)\.){3}([0-9]*?):([0-9]*?)$#si';

        $this->assertRegExp($pattern, $socket->getLocalAddress());
    }

    /**
     *
     */
    public function testApiGetRemoteAddress_ReturnsAddress()
    {
        $remote = $this->tempSocketRemoteAddress();
        $server = stream_socket_server($remote);
        $socket = $this->createSocketMock($remote);

        $address = str_replace('tcp://', '', $remote);

        $this->assertEquals($address, $socket->getRemoteAddress());
    }

    /**
     *
     */
    public function testApiGetLocalHost_ReturnsHost()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $socket = $this->createSocketMock($this->tempSocketRemoteAddress());

        $pattern = '#^(([0-9]*?)\.){3}([0-9]*?)$#si';

        $this->assertRegExp($pattern, $socket->getLocalHost());
    }

    /**
     *
     */
    public function testApiGetRemoteHost_ReturnsHost()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $socket = $this->createSocketMock($this->tempSocketRemoteAddress());

        $pattern = '#^(([0-9]*?)\.){3}([0-9]*?)$#si';

        $this->assertRegExp($pattern, $socket->getRemoteHost());
    }

    /**
     *
     */
    public function testApiGetLocalPort_ReturnsPort()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $socket = $this->createSocketMock($this->tempSocketRemoteAddress());

        $pattern = '#^([0-9]*?)$#si';

        $this->assertRegExp($pattern, $socket->getLocalPort());
    }

    /**
     *
     */
    public function testApiGetRemotePort_ReturnsPort()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $socket = $this->createSocketMock($this->tempSocketRemoteAddress());

        $pattern = '#^([0-9]*?)$#si';

        $this->assertRegExp($pattern, $socket->getRemotePort());
    }

    public function testApiGetLocalProtocol_ReturnsProtocol()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $socket = $this->createSocketMock($this->tempSocketRemoteAddress());

        $transports = stream_get_transports();
        $this->assertTrue(in_array($socket->getLocalProtocol(), $transports));
    }

    public function testApiGetRemoteProtocol_ReturnsProtocol()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $socket = $this->createSocketMock($this->tempSocketRemoteAddress());

        $transports = stream_get_transports();

        $this->assertTrue(in_array($socket->getRemoteProtocol(), $transports));
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface $loop
     * @param array $config
     * @return Socket
     */
    protected function createSocketMock($resource = null, LoopInterface $loop = null, $config = [])
    {
        return $this->createSocketInjection(
            is_null($resource) ? $this->tempSocketAddress() : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop, $config
        );
    }

    /**
     * @param string|resource $endpointOrResource
     * @param LoopInterface $loop
     * @param array $config
     * @return Socket
     */
    protected function createSocketInjection($endpointOrResource, LoopInterface $loop, $config = [])
    {
        return new Socket($endpointOrResource, $loop ,$config);
    }

    /**
     * @return string
     */
    private function tempSocketAddress()
    {
        return 'unix://' . $this->basePath() . '/temp.sock';
    }

    /**
     * @return string
     */
    private function tempSocketRemoteAddress()
    {
        return 'tcp://127.0.0.1:10080';
    }
}
