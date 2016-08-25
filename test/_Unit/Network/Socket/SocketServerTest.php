<?php

namespace Kraken\_Unit\Network\Socket;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use Kraken\Ipc\Socket\SocketListenerInterface;
use Kraken\Network\Null\NullServer;
use Kraken\Network\Socket\SocketServer;
use Kraken\Network\Socket\SocketServerInterface;
use Kraken\Network\ServerComponentAwareInterface;
use Kraken\Network\ServerComponentInterface;
use Kraken\Network\NetworkConnection;
use Kraken\Network\NetworkMessage;
use Kraken\Test\TUnit;
use Exception;

class SocketServerTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $server = $this->createSocketServer($listener, $component);

        $this->assertInstanceOf(SocketServer::class, $server);
        $this->assertInstanceOf(SocketServerInterface::class, $server);
        $this->assertInstanceOf(ServerComponentAwareInterface::class, $server);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $server = $this->createSocketServer($listener, $component);

        unset($server);
    }

    /**
     *
     */
    public function testApiSetComponent_SetsComponent_WhenComponentIsProvided()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $server = $this->createSocketServer($listener, $component);

        $server->setComponent($new = $this->createComponent());
        $this->assertSame($new, $server->getComponent());
    }

    /**
     *
     */
    public function testApiSetComponent_SetsNullComponent_WhenComponentIsNotProvided()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $server = $this->createSocketServer($listener, $component);

        $server->setComponent();
        $this->assertInstanceOf(NullServer::class, $server->getComponent());
    }

    /**
     *
     */
    public function testApiGetComponent_ReturnsComponent()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $server = $this->createSocketServer($listener, $component);

        $this->assertSame($component, $server->getComponent());
    }

    /**
     *
     */
    public function testApiHandleConnect_CallsMethodOnComponent()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleConnect')
            ->with($this->isInstanceOf(NetworkConnection::class));

        $socket = $socket = $this->getMock(SocketInterface::class, [], [], '', false);

        $server = $this->createSocketServer($listener, $component);

        $server->handleConnect($listener, $socket);
    }

    /**
     *
     */
    public function testApiHandleConnect_AttachesHandlers()
    {
        $events = [];
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleConnect');

        $listener = $this->createListener();

        $socket = $this->getMock(SocketInterface::class, [], [], '', false);
        $socket
            ->expects($this->exactly(3))
            ->method('on')
            ->will($this->returnCallback(function($event, $handler) use(&$events) {
                $events[] = $event;
            }));

        $server = $this->createSocketServer($listener, $component);

        $server->handleConnect($listener, $socket);

        $this->assertSame([ 'data', 'error', 'close' ], $events);
    }

    /**
     *
     */
    public function testApiHandleConnect_ClosesConnection_WhenComponentThrowsException()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleConnect')
            ->with($this->isInstanceOf(NetworkConnection::class))
            ->will($this->throwException(new Exception));

        $socket = $this->getMock(SocketInterface::class, [], [], '', false);

        $server = $this->createSocketServer($listener, $component, [ 'close' ]);
        $server
            ->expects($this->once())
            ->method('close')
            ->with($socket);

        $server->handleConnect($listener, $socket);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_CallsMethodOnComponent()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleDisconnect')
            ->with($this->isInstanceOf(NetworkConnection::class));

        $socket = $this->getMock(SocketInterface::class, [], [], '', false);
        $socket->conn = new NetworkConnection($socket);

        $server = $this->createSocketServer($listener, $component);

        $server->handleDisconnect($socket);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_TriesToHandleError_WhenComponentThrowsException()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleDisconnect')
            ->with($this->isInstanceOf(NetworkConnection::class))
            ->will($this->throwException($ex = new Exception));

        $socket = $this->getMock(SocketInterface::class, [], [], '', false);
        $socket->conn = new NetworkConnection($socket);

        $server = $this->createSocketServer($listener, $component, [ 'handleError' ]);
        $server
            ->expects($this->once())
            ->method('handleError')
            ->with($socket, $ex);

        $server->handleDisconnect($socket);
    }

    /**
     *
     */
    public function testApiHandleData_CallsMethodOnComponent()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleMessage')
            ->with(
                $this->isInstanceOf(NetworkConnection::class),
                $this->isInstanceOf(NetworkMessage::class)
            );

        $socket = $this->getMock(SocketInterface::class, [], [], '', false);
        $socket->conn = new NetworkConnection($socket);

        $server = $this->createSocketServer($listener, $component);

        $server->handleData($socket, 'data');
    }

    /**
     *
     */
    public function testApiHandleData_TriesToHandleError_WhenComponentThrowsException()
    {
        $data = 'data';

        $listener  = $this->createListener();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleMessage')
            ->with(
                $this->isInstanceOf(NetworkConnection::class),
                $this->isInstanceOf(NetworkMessage::class)
            )
            ->will($this->throwException($ex = new Exception));

        $socket = $this->getMock(SocketInterface::class, [], [], '', false);
        $socket->conn = new NetworkConnection($socket);

        $server = $this->createSocketServer($listener, $component, [ 'handleError' ]);
        $server
            ->expects($this->once())
            ->method('handleError')
            ->with($socket, $ex);

        $server->handleData($socket, $data);
    }

    /**
     *
     */
    public function testApiHandleError_CallsMethodOnComponent()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleError')
            ->with(
                $this->isInstanceOf(NetworkConnection::class),
                $this->isInstanceOf(Exception::class)
            );

        $socket = $this->getMock(SocketInterface::class, [], [], '', false);
        $socket->conn = new NetworkConnection($socket);

        $server = $this->createSocketServer($listener, $component);

        $server->handleError($socket, new Exception);
    }

    /**
     *
     */
    public function testApiHandleError_TriesToHandleError_WhenComponentThrowsException()
    {
        $ex1 = new Exception();
        $ex2 = new Exception();

        $listener  = $this->createListener();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleError')
            ->with(
                $this->isInstanceOf(NetworkConnection::class),
                $ex2
            )
            ->will($this->throwException($ex1));

        $socket = $this->getMock(SocketInterface::class, [], [], '', false);
        $socket->conn = new NetworkConnection($socket);

        $server = $this->createSocketServer($listener, $component, [ 'close' ]);
        $server
            ->expects($this->once())
            ->method('close')
            ->with($socket);

        $server->handleError($socket, $ex2);
    }

    /**
     *
     */
    public function testProtectedApiClose_ClosesSocket()
    {
        $listener  = $this->createListener();
        $component = $this->createComponent();
        $server = $this->createSocketServer($listener, $component);

        $socket = $this->getMock(SocketInterface::class, [], [], '', false);
        $socket
            ->expects($this->once())
            ->method('close');

        $this->callProtectedMethod($server, 'close', [ $socket ]);
    }

    /**
     * @return ServerComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createComponent()
    {
        return $this->getMock(ServerComponentInterface::class, [], [], '', false);
    }

    /**
     * @return SocketListenerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createListener()
    {
        return $this->getMock(SocketListener::class, [], [], '', false);
    }

    /**
     * @param SocketListenerInterface $listener
     * @param ServerComponentInterface $component
     * @param string[]|null $methods
     * @return SocketServer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSocketServer($listener, $component, $methods = null)
    {
        return $this->getMock(SocketServer::class, $methods, [ $listener, $component ]);
    }
}
