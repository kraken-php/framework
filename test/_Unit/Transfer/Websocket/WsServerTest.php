<?php

namespace Kraken\_Unit\Transfer\Socket;

use Kraken\Transfer\Http\HttpRequest;
use Kraken\Transfer\Http\HttpResponseInterface;
use Kraken\Transfer\Null\NullServer;
use Kraken\Transfer\Websocket\Driver\Version\RFC6455\Version;
use Kraken\Transfer\Websocket\Driver\WsDriverInterface;
use Kraken\Transfer\Websocket\WsServer;
use Kraken\Transfer\Websocket\WsServerInterface;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\TransferMessage;
use Kraken\Transfer\TransferComponentAwareInterface;
use Kraken\Transfer\TransferComponentInterface;
use Kraken\Test\TUnit;
use Exception;
use StdClass;

class WsServerTest extends TUnit
{
    /**
     * @var WsServer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $server;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $this->assertInstanceOf(WsServer::class, $server);
        $this->assertInstanceOf(WsServerInterface::class, $server);
        $this->assertInstanceOf(TransferComponentAwareInterface::class, $server);
        $this->assertInstanceOf(TransferComponentInterface::class, $server);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        unset($server);
    }

    /**
     *
     */
    public function testApiSetComponent_SetsComponent_WhenComponentIsProvided()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $server->setComponent($new = $this->createComponent());
        $this->assertSame($new, $server->getComponent());
    }

    /**
     *
     */
    public function testApiSetComponent_SetsNullComponent_WhenComponentIsNotProvided()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $server->setComponent();
        $this->assertInstanceOf(NullServer::class, $server->getComponent());
    }

    /**
     *
     */
    public function testApiGetComponent_ReturnsComponent()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $this->assertSame($component, $server->getComponent());
    }

    /**
     *
     */
    public function testApiGetDriver_ReturnsDriver()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $this->assertInstanceOf(WsDriverInterface::class, $server->getDriver());
    }

    /**
     *
     */
    public function testApiHandleConnect_SetsConnectionFlags()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component, [ 'attemptUpgrade' ]);
        $server
            ->expects($this->once())
            ->method('attemptUpgrade');

        $req  = $this->getMock(HttpRequest::class, [], [], '', false);
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $conn->httpRequest = $req;

        $server->handleConnect($conn);

        $this->assertInstanceOf(StdClass::class, $conn->WebSocket);
        $this->assertSame($req,  $conn->WebSocket->request);
        $this->assertSame(false, $conn->WebSocket->established);
        $this->assertSame(false, $conn->WebSocket->closing);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_CallsMethodOnComponent_WhenConnectionDoesExist()
    {
        $conn     = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $upgraded = $this->getMock(TransferConnectionInterface::class, [], [], '', false);

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleDisconnect')
            ->with($upgraded);

        $server = $this->createServer($aware, $component);

        $storage = $this->getProtectedProperty($server, 'connCollection');
        $storage->attach($conn, $upgraded);

        $server->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_DoesNothing_WhenConnectionDoesNotExist()
    {
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->never())
            ->method('handleDisconnect');

        $server = $this->createServer($aware, $component);

        $server->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleError_CallsMethodOnComponent_WhenConnectionIsRegisteredAndIsEstablished()
    {
        $ex = new Exception();
        $conn     = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $upgraded = $this->getMock(TransferConnectionInterface::class, [], [], '', false);

        $conn->WebSocket = new StdClass();
        $conn->WebSocket->established = true;

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleError')
            ->with($upgraded, $ex);

        $server = $this->createServer($aware, $component);

        $storage = $this->getProtectedProperty($server, 'connCollection');
        $storage->attach($conn, $upgraded);

        $server->handleError($conn, $ex);
    }

    /**
     *
     */
    public function testApiHandleError_ClosesConnection_WhenConnectionIsRegisteredButIsNotEstablished()
    {
        $ex = new Exception();
        $conn     = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $upgraded = $this->getMock(TransferConnectionInterface::class, [], [], '', false);

        $conn
            ->expects($this->once())
            ->method('close');

        $conn->WebSocket = new StdClass();
        $conn->WebSocket->established = false;

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->never())
            ->method('handleError');

        $server = $this->createServer($aware, $component);

        $storage = $this->getProtectedProperty($server, 'connCollection');
        $storage->attach($conn, $upgraded);

        $server->handleError($conn, $ex);
    }

    /**
     *
     */
    public function testApiHandleError_ClosesConnection_WhenConnectionIsNotRegisteredButIsEstablished()
    {
        $ex = new Exception();
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('close');

        $conn->WebSocket = new StdClass();
        $conn->WebSocket->established = true;

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->never())
            ->method('handleError');

        $server = $this->createServer($aware, $component);

        $server->handleError($conn, $ex);
    }

    /**
     *
     */
    public function testApiHandleMessage_DoesNothing_WhenReceivedHttpRequest()
    {
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $mssg = new HttpRequest('GET', '/');

        $aware     = $this->createAware();
        $component = $this->createComponent();

        $server = $this->createServer($aware, $component, [ 'attemptUpgrade' ]);
        $server
            ->expects($this->never())
            ->method('attemptUpgrade');

        $server->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_DoesNothing_WhenReceivedDataDuringSocketClosure()
    {
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $mssg = new TransferMessage('Message');

        $conn->WebSocket = new StdClass();
        $conn->WebSocket->closing = true;

        $aware     = $this->createAware();
        $component = $this->createComponent();

        $server = $this->createServer($aware, $component, [ 'attemptUpgrade' ]);
        $server
            ->expects($this->never())
            ->method('attemptUpgrade');

        $server->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_PropagatesMessage_WhenReceivedEstablishedConnection()
    {
        $mssg = new TransferMessage('Message');
        $conn     = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $upgraded = $this->getMock(TransferConnectionInterface::class, [], [], '', false);

        $version = $this->getMock(Version::class, [], [], '', false);
        $version
            ->expects($this->once())
            ->method('wsMessage')
            ->with($upgraded, $mssg);

        $conn->WebSocket = new StdClass();
        $conn->WebSocket->closing = false;
        $conn->WebSocket->established = true;
        $conn->WebSocket->version = $version;

        $aware     = $this->createAware();
        $component = $this->createComponent();

        $server = $this->createServer($aware, $component, [ 'attemptUpgrade' ]);
        $server
            ->expects($this->never())
            ->method('attemptUpgrade');

        $storage = $this->getProtectedProperty($server, 'connCollection');
        $storage->attach($conn, $upgraded);

        $server->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_AttemptsUpgarde_WhenReceivedNotEstablishedConnection()
    {
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $mssg = new TransferMessage('Message');

        $conn->WebSocket = new StdClass();
        $conn->WebSocket->closing = false;
        $conn->WebSocket->established = false;

        $aware     = $this->createAware();
        $component = $this->createComponent();

        $server = $this->createServer($aware, $component, [ 'attemptUpgrade' ]);
        $server
            ->expects($this->once())
            ->method('attemptUpgrade')
            ->with($conn);

        $server->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testProtectedApiClose_ClosesSocket()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $code = 300;
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(HttpResponseInterface::class));
        $conn
            ->expects($this->once())
            ->method('close');

        $this->callProtectedMethod($server, 'close', [ $conn, $code ]);
    }

    /**
     * @return TransferComponentAwareInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createAware()
    {
        return $this->getMock(TransferComponentAwareInterface::class, [], [], '', false);
    }

    /**
     * @return TransferComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createComponent()
    {
        return $this->getMock(TransferComponentInterface::class, [], [], '', false);
    }

    /**
     * @param TransferComponentAwareInterface $aware
     * @param TransferComponentInterface $component
     * @param string[]|null $methods
     * @return WsServer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createServer($aware, $component, $methods = null)
    {
        $this->server = $this->getMock(WsServer::class, $methods, [ $aware, $component ]);

        return $this->server;
    }
}
