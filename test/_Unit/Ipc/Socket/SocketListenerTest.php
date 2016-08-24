<?php

namespace Kraken\_Unit\Ipc\Socket;

use Kraken\Ipc\Socket\SocketListener;
use Kraken\Ipc\Socket\SocketListenerInterface;
use Kraken\Loop\LoopInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Test\TUnit;

class SocketListenerTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $socket = $this->createSocketListenerMock();

        $this->assertInstanceOf(SocketListener::class, $socket);
        $this->assertInstanceOf(SocketListenerInterface::class, $socket);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $socket = $this->createSocketListenerMock();
        unset($socket);
    }

    /**
     *
     */
    public function testConstructor_ThrowsException_OnInvalidResource()
    {
        $this->setExpectedException(InstantiationException::class);
        $socket = $this->createSocketListenerMock('invalid');
    }

    /**
     *
     */
    public function testConstructor_ThrowsException_OnOccupiedEndpoint()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $this->setExpectedException(InstantiationException::class);
        $socket = $this->createSocketListenerMock();
    }

    /**
     *
     */
    public function testApiGetLocalEndpoint_ReturnsEndpoint()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertRegExp('#^tcp://(([0-9]*?)\.){3}([0-9]*?):([0-9]*?)$#si', $socket->getLocalEndpoint());
    }

    /**
     *
     */
    public function testApiGetResource_ReturnsResource()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertTrue(is_resource($socket->getResource()));
    }

    /**
     *
     */
    public function testApiGetResourceId_ReturnsResourceId()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertTrue(is_numeric($socket->getResourceId()));
    }

    /**
     *
     */
    public function testApiGetMetadata_ReturnsMetadata()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertTrue(is_array($socket->getMetadata()));
    }

    /**
     *
     */
    public function testApiGetStreamType_ReturnsStreamType()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertSame('tcp_socket/ssl', $socket->getStreamType());
    }

    /**
     *
     */
    public function testApiGetWrapperType_ReturnsWrapperType()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertSame('undefined', $socket->getWrapperType());
    }

    /**
     *
     */
    public function testApiIsOpen_ReturnsIfSocketIsOpened()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertTrue($socket->isOpen());
        $socket->close();
        $this->assertFalse($socket->isOpen());
    }

    /**
     *
     */
    public function testApiIsPaused_ReturnsIfSocketIsPaused()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertFalse($socket->isPaused());
        $socket->pause();
        $this->assertTrue($socket->isPaused());
    }

    /**
     *
     */
    public function testApiClose_ClosesSocket()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertTrue($socket->isOpen());
        $socket->close();
        $this->assertFalse($socket->isOpen());
    }

    /**
     *
     */
    public function testApiPause_PausesSocket()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertFalse($socket->isPaused());
        $socket->pause();
        $this->assertTrue($socket->isPaused());
    }

    /**
     *
     */
    public function testApiResume_ResumesSocket()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());

        $socket->pause();
        $this->assertTrue($socket->isPaused());

        $socket->resume();
        $this->assertFalse($socket->isPaused());
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface $loop
     * @return SocketListener
     */
    protected function createSocketListenerMock($resource = null, LoopInterface $loop = null)
    {
        return $this->createSocketListenerInjection(
            is_null($resource) ? $this->tempSocketRemoteAddress() : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }

    /**
     * @param string|resource $endpointOrResource
     * @param LoopInterface $loop
     * @return SocketListener
     */
    protected function createSocketListenerInjection($endpointOrResource, LoopInterface $loop)
    {
        return new SocketListener($endpointOrResource, $loop);
    }

    /**
     * @return string
     */
    private function tempSocketRemoteAddress()
    {
        return 'tcp://127.0.0.1:10080';
    }
}
