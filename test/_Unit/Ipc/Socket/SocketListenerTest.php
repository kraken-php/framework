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
    public function testStart_ThrowsException_OnInvalidResource()
    {
        $this->setExpectedException(InstantiationException::class);
        $socket = $this->createSocketListenerMock('invalid');
        $socket->start();
    }

    /**
     *
     */
    public function testStart_ThrowsException_OnOccupiedEndpoint()
    {
        $server = stream_socket_server($this->tempSocketRemoteAddress());
        $this->setExpectedException(InstantiationException::class);
        $socket = $this->createSocketListenerMock();
        $socket->start();
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
    public function testApiGetLocalAddress_ReturnsAddress()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());

        $pattern = '#^(([0-9]*?)\.){3}([0-9]*?):([0-9]*?)$#si';

        $this->assertRegExp($pattern, $socket->getLocalAddress());
    }

    /**
     *
     */
    public function testApiGetLocalHost_ReturnsHost()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());

        $pattern = '#^(([0-9]*?)\.){3}([0-9]*?)$#si';

        $this->assertRegExp($pattern, $socket->getLocalHost());
    }

    /**
     *
     */
    public function testApiGetLocalPort_ReturnsPort()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());

        $pattern = '#^([0-9]*?)$#si';

        $this->assertRegExp($pattern, $socket->getLocalPort());
    }

    /**
     *
     */
    public function testApiGetResource_ReturnsResource()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $socket->start();
        $this->assertTrue(is_resource($socket->getResource()));
    }

    /**
     *
     */
    public function testApiGetResourceId_ReturnsResourceId()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $socket->start();
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
        $socket->start();
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
        $this->assertFalse($socket->isOpen());
        $socket->start();
        $this->assertTrue($socket->isOpen());
    }

    /**
     *
     */
    public function testApiIsPaused_ReturnsIfSocketIsPaused()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertTrue($socket->isPaused());
        $socket->start();
        $this->assertFalse($socket->isPaused());
    }

    //todo
    public function testApiStart_CannotBeInvokedMoreThanOnce()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
    }

    //todo
    public function testApiStop_CannotBeInvokedMoreThanOnce()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
    }

    //todo
    public function testApiStart_CanBeInvokedAfterStop()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());

    }

    //todo
    public function testApiStop_CanBeInvokedAfterStart()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
    }

    /**
     *
     */
    public function testApiStart_StartSocket()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $this->assertFalse($socket->isOpen());
        $socket->start();
        $this->assertTrue($socket->isOpen());
    }

    /**
     *
     */
    public function testApiClose_ClosesSocket()
    {
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress());
        $socket->start();
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
        $socket->start();
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
     * @throws InstantiationException
     */
    public function testApiStart_SslSocket()
    {
        $config = [
            'ssl' => true,
            'ssl_cert' => __DIR__ . '/_Data/_ssl_cert.pem',
            'ssl_secret' => __DIR__ . '/_Data/_ssl_key.pem',
            'ssl_key'=> 'secret',
        ];
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress(), null, $config);

        $this->assertFalse($socket->isOpen());
        $socket->start();

        $this->assertTrue($socket->isOpen());
    }

    /**
     * @throws InstantiationException
     */
    public function testApiClose_SslSocket()
    {
        $config = [
            'ssl' => true,
            'ssl_cert' => __DIR__ . '/_Data/_ssl_cert.pem',
            'ssl_secret' => __DIR__ . '/_Data/_ssl_key.pem',
            'ssl_key'=> 'secret',
        ];
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress(), null, $config);
        $socket->start();

        $this->assertTrue($socket->isOpen());
        $socket->close();

        $this->assertFalse($socket->isOpen());
    }

    /**
     * @throws InstantiationException
     */
    public function testApiPause_SslSocket()
    {
        $config = [
            'ssl' => true,
            'ssl_cert' => __DIR__ . '/_Data/_ssl_cert.pem',
            'ssl_secret' => __DIR__ . '/_Data/_ssl_key.pem',
            'ssl_key'=> 'secret',
        ];
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress(), null, $config);
        $socket->start();

        $this->assertFalse($socket->isPaused());
        $socket->pause();

        $this->assertTrue($socket->isPaused());
    }

    /**
     * @throws InstantiationException
     */
    public function testApiResume_SslSocket()
    {
        $config = [
            'ssl' => true,
            'ssl_cert' => __DIR__ . '/_Data/_ssl_cert.pem',
            'ssl_secret' => __DIR__ . '/_Data/_ssl_key.pem',
            'ssl_key'=> 'secret',
        ];
        $socket = $this->createSocketListenerMock($this->tempSocketRemoteAddress(), null, $config);
        $socket->pause();
        $this->assertTrue($socket->isPaused());

        $socket->resume();
        $this->assertFalse($socket->isPaused());
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface $loop
     * @param array $config
     * @return SocketListener
     */
    protected function createSocketListenerMock($resource = null, LoopInterface $loop = null , $config = [])
    {
        return $this->createSocketListenerInjection(
            is_null($resource) ? $this->tempSocketRemoteAddress() : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop , $config
        );
    }

    /**
     * @param string|resource $endpointOrResource
     * @param LoopInterface $loop
     * @param array $config
     * @return SocketListener
     */
    protected function createSocketListenerInjection($endpointOrResource, LoopInterface $loop , $config = [])
    {
        return new SocketListener($endpointOrResource, $loop, $config);
    }

    /**
     * @return string
     */
    private function tempSocketRemoteAddress()
    {
        return 'tcp://127.0.0.1:10080';
    }
}
