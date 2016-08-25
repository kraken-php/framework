<?php

namespace Kraken\_Unit\Network\Http\Component\Session;

use Kraken\Network\Http\Component\Session\HttpSession;
use Kraken\Network\Http\Component\Session\HttpSessionInterface;
use Kraken\Network\Http\HttpRequest;
use Kraken\Network\Http\HttpServer;
use Kraken\Network\Null\NullServer;
use Kraken\Network\Socket\SocketServer;
use Kraken\Network\ServerComponentAwareInterface;
use Kraken\Network\ServerComponentInterface;
use Kraken\Test\TUnit;
use Kraken\Network\NetworkConnection;
use Kraken\Network\NetworkMessage;
use PDO;
use stdClass;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Exception;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class HttpSessionTest extends TUnit
{
    /**
     *
     */
    public function tearDown()
    {
        ini_set('session.serialize_handler', 'php');

        parent::tearDown();
    }

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $handler   = $this->createSessionHandler();
        $session   = $this->createSession($server, $component, $handler);

        $this->assertInstanceOf(HttpSession::class, $session);
        $this->assertInstanceOf(HttpSessionInterface::class, $session);
        $this->assertInstanceOf(ServerComponentAwareInterface::class, $session);
        $this->assertInstanceOf(ServerComponentInterface::class, $session);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $handler   = $this->createSessionHandler();
        $session   = $this->createSession($server, $component, $handler);

        unset($session);
    }

    /**
     *
     */
    public function testApiSetComponent_SetsComponent_WhenComponentIsProvided()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $handler   = $this->createSessionHandler();
        $session   = $this->createSession($server, $component, $handler);

        $session->setComponent($new = $this->createComponent());
        $this->assertSame($new, $session->getComponent());
    }

    /**
     *
     */
    public function testApiSetComponent_SetsNullComponent_WhenComponentIsNotProvided()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $handler   = $this->createSessionHandler();
        $session   = $this->createSession($server, $component, $handler);

        $session->setComponent();
        $this->assertInstanceOf(NullServer::class, $session->getComponent());
    }

    /**
     *
     */
    public function testApiGetComponent_ReturnsComponent()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $handler   = $this->createSessionHandler();
        $session   = $this->createSession($server, $component, $handler);

        $this->assertSame($component, $session->getComponent());
    }

    /**
     *
     */
    public function testApiHandleConnect_AttachesSessionHandler()
    {
        if (!extension_loaded('PDO') || !extension_loaded('pdo_sqlite'))
        {
            $this->markTestSkipped('Session test requires PDO and pdo_sqlite');
        }

        $sessionId = md5('testSession');

        $dbOptions = [
            'db_table'        => 'sessions',
            'db_id_col'       => 'sess_id',
            'db_data_col'     => 'sess_data',
            'db_time_col'     => 'sess_time',
            'db_lifetime_col' => 'sess_lifetime'
        ];

        $pdo = new PDO("sqlite::memory:");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            vsprintf("CREATE TABLE %s (%s TEXT NOT NULL PRIMARY KEY, %s BLOB NOT NULL, %s INTEGER NOT NULL, %s INTEGER)", $dbOptions)
        );

        $pdoHandler = new PdoSessionHandler($pdo, $dbOptions);
        $pdoHandler->write($sessionId, '_sf2_attributes|a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}_sf2_flashes|a:0:{}');

        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);

        $server    = $this->createServer();
        $component = $this->createComponent([ 'handleConnect' ]);
        $component
            ->expects($this->once())
            ->method('handleConnect')
            ->with($conn);
        $session   = $this->createSession($server, $component, $pdoHandler, [ 'auto_start' => 1 ]);

        $req = $this->getMock(HttpRequest::class, [ 'getCookie' ], [ 'POST', '/', [] ]);
        $req
            ->expects($this->once())
            ->method('getCookie')
            ->with(ini_get('session.name'))
            ->will($this->returnValue($sessionId));

        $conn->WebSocket = new StdClass;
        $conn->WebSocket->request = $req;

        $session->handleConnect($conn);

        $this->assertSame('world', $conn->Session->get('hello'));
    }

    /**
     *
     */
    public function testApiHandleDisconnect_PropagatesDisconnection()
    {
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);

        $server    = $this->createServer();
        $component = $this->createComponent([ 'handleDisconnect' ]);
        $component
            ->expects($this->once())
            ->method('handleDisconnect')
            ->with($conn);
        $handler   = $this->createSessionHandler();
        $session   = $this->createSession($server, $component, $handler, []);

        $session->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleMessage_PropagatesMessage()
    {
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);
        $mssg = $this->getMock(NetworkMessage::class, [], [], '', false);

        $server    = $this->createServer();
        $component = $this->createComponent([ 'handleMessage' ]);
        $component
            ->expects($this->once())
            ->method('handleMessage')
            ->with($conn, $mssg);
        $handler   = $this->createSessionHandler();
        $session   = $this->createSession($server, $component, $handler, []);

        $session->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleError_PropagatesError()
    {
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);
        $ex   = new Exception();

        $server    = $this->createServer();
        $component = $this->createComponent([ 'handleError' ]);
        $component
            ->expects($this->once())
            ->method('handleError')
            ->with($conn, $ex);
        $handler   = $this->createSessionHandler();
        $session   = $this->createSession($server, $component, $handler, []);

        $session->handleError($conn, $ex);
    }

    /**
     * @param string[]|null $methods
     * @return ServerComponentAwareInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createServer($methods = [])
    {
        $server = $this->getMock(SocketServer::class, [], [], '', false);

        return $this->getMock(HttpServer::class, $methods, [ $server ]);
    }

    /**
     * @return ServerComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createComponent($methods = [])
    {
        return $this->getMock(NullServer::class, $methods, []);
    }

    /**
     * @return NullSessionHandler
     */
    public function createSessionHandler()
    {
        return new NullSessionHandler();
    }

    /**
     * @param ServerComponentAwareInterface $aware
     * @param ServerComponentInterface $component
     * @param SessionHandlerInterface $handler
     * @param mixed[] $options
     * @param string[]|null $methods
     * @return HttpSession|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSession($aware, $component, $handler, $options = [], $methods = null)
    {
        return $this->getMock(HttpSession::class, $methods, [ $aware, $component, $handler, $options ]);
    }
}
