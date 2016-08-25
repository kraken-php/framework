<?php

namespace Kraken\_Unit\Transfer\Http\Component\Router;

use Kraken\Transfer\Http\Component\Router\HttpRouter;
use Kraken\Transfer\Http\Component\Router\HttpRouterInterface;
use Kraken\Transfer\Http\HttpRequest;
use Kraken\Transfer\Http\HttpResponseInterface;
use Kraken\Transfer\Http\HttpServer;
use Kraken\Transfer\Null\NullServer;
use Kraken\Transfer\Socket\SocketServer;
use Kraken\Transfer\ServerComponentAwareInterface;
use Kraken\Transfer\ServerComponentInterface;
use Kraken\Transfer\TransferConnection;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\TransferMessage;
use Kraken\Test\TUnit;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Exception;

class HttpRouterTest extends TUnit
{
    /**
     * @var HttpRouter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $this->assertInstanceOf(HttpRouter::class, $router);
        $this->assertInstanceOf(HttpRouterInterface::class, $router);
        $this->assertInstanceOf(ServerComponentInterface::class, $router);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        unset($router);
    }

    /**
     *
     */
    public function testApiAllowOrigin_BlocksAddress()
    {
        $ip = '50.50.50.50';

        $server = $this->createServer();
        $router = $this->createRouter($server);

        $this->assertFalse(array_key_exists($ip, $this->getProtectedProperty($router, 'allowedOrigins')));
        $router->allowOrigin($ip);
        $this->assertTrue(array_key_exists($ip, $this->getProtectedProperty($router, 'allowedOrigins')));
    }

    /**
     *
     */
    public function testApiDisallowOrigin_UnblocksAddress()
    {
        $ip = '50.50.50.50';

        $server = $this->createServer();
        $router = $this->createRouter($server);

        $router->allowOrigin($ip);
        $this->assertTrue(array_key_exists($ip, $this->getProtectedProperty($router, 'allowedOrigins')));

        $router->disallowOrigin($ip);
        $this->assertFalse(array_key_exists($ip, $this->getProtectedProperty($router, 'allowedOrigins')));
    }

    /**
     *
     */
    public function testApiIsOriginAllowed_ReturnsFalse_WhenIpIsNotBlocked()
    {
        $ip = '50.50.50.50';

        $server = $this->createServer();
        $router = $this->createRouter($server);

        $router->allowOrigin($ip);
        $router->disallowOrigin($ip);

        $this->assertFalse($router->isOriginAllowed($ip));
    }

    /**
     *
     */
    public function testApiIsOriginAllowed_ReturnsTrue_WhenIpIsBlocked()
    {
        $ip = '50.50.50.50';

        $server = $this->createServer();
        $router = $this->createRouter($server);

        $router->allowOrigin($ip);
        $this->assertTrue($router->isOriginAllowed($ip));
    }

    /**
     *
     */
    public function testApiGetAllowedOrigins()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $router->allowOrigin($ip1 = '50.25.25.25');
        $router->allowOrigin($ip2 = '50.50.50.50');

        $this->assertSame([ $ip1, $ip2 ], $router->getAllowedOrigins());
    }

    /**
     *
     */
    public function testApiExistsRoute_ReturnsFalse_WhenRouteDoesNotExist()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $route  = '/route';

        $this->assertFalse($router->existsRoute($route));
    }

    /**
     *
     */
    public function testApiExistsRoute_ReturnsTrue_WhenRouteDoesExist()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $route  = '/route';
        $router->addRoute($route, $this->createComponent());

        $this->assertTrue($router->existsRoute($route));
    }

    /**
     *
     */
    public function testApiAddRoute_AddsRoute_WhenRouteDoesNotExist()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $route  = '/route';

        $this->assertFalse($router->existsRoute($route));
        $router->addRoute($route, $this->createComponent());
        $this->assertTrue($router->existsRoute($route));
    }

    /**
     *
     */
    public function testApiAddRoute_ReplacesRoute_WhenRouteDoesExist()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $route  = '/route';

        $this->assertFalse($router->existsRoute($route));
        $router->addRoute($route, $this->createComponent());
        $router->addRoute($route, $this->createComponent());
        $this->assertTrue($router->existsRoute($route));
    }

    /**
     *
     */
    public function testApiRemoveRoute_DoesNothing_WhenRouteDoesNotExist()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $route  = '/route';

        $router->removeRoute($route);
        $this->assertFalse($router->existsRoute($route));
    }

    /**
     *
     */
    public function testApiRemoveRoute_RemovesRoute_WhenRouteDoesExist()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $route  = '/route';

        $router->addRoute($route, $this->createComponent());
        $this->assertTrue($router->existsRoute($route));

        $router->removeRoute($route);
        $this->assertFalse($router->existsRoute($route));
    }

    /**
     *
     */
    public function testApiHandleConnect_DoesNothing()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $conn = $this->getMock(TransferConnection::class, [], [], '', false);

        $router->handleConnect($conn);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_DoesNothing_WhenConnectionControllerDoesNotExist()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $conn = $this->getMock(TransferConnection::class, [], [], '', false);

        $router->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_PropagatesDisconnection_WhenConnectionControllerDoesExist()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $conn->controller = $this->createComponent();
        $conn->controller
            ->expects($this->once())
            ->method('handleDisconnect')
            ->with($conn);

        $router->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleMessage_ClosesConnectionWithCode500_WhenNotHttpRequestReceived_AndControllerDoesNotExist()
    {
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $mssg = $this->getMock(TransferMessage::class, [], [], '', false);

        $server = $this->createServer();
        $router = $this->createRouter($server, [ 'close' ]);
        $router
            ->expects($this->once())
            ->method('close')
            ->with($conn, 500);

        $router->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_PropagatesMessage_WhenNotHttpRequestReceived_AndControllerDoesExist()
    {
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $mssg = $this->getMock(TransferMessage::class, [], [], '', false);

        $server = $this->createServer();
        $router = $this->createRouter($server);

        $conn->controller = $this->createComponent();
        $conn->controller
            ->expects($this->once())
            ->method('handleMessage')
            ->with($conn, $mssg);

        $router->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_ClosesConnectionWithCode403_WhenHttpRequestReceived_ButOriginIsNotAllowed()
    {
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $mssg = $this->getMock(HttpRequest::class, [], [], '', false);
        $mssg
            ->expects($this->once())
            ->method('getHeaderLine')
            ->with('Origin')
            ->will($this->returnValue('origin'));

        $server = $this->createServer();
        $router = $this->createRouter($server, [ 'close' ]);
        $router
            ->expects($this->once())
            ->method('close')
            ->with($conn, 403);

        $router->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_ClosesConnectionWithCode403_WhenHttpRequestReceived_ButMatcherThrowsMethodNotAllowedException()
    {
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $mssg = $this->createPassableMessage();

        $server = $this->createServer();
        $router = $this->createRouter($server, [ 'close' ]);
        $router
            ->expects($this->once())
            ->method('close')
            ->with($conn, 403);

        $matcher = $this->createMatcher([ 'match' ]);
        $matcher
            ->expects($this->once())
            ->method('match')
            ->will($this->throwException(new MethodNotAllowedException([])));

        $router->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_ClosesConnectionWithCode404_WhenHttpRequestReceived_ButMatcherThrowsResourceNotFoundException()
    {
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $mssg = $this->createPassableMessage();

        $server = $this->createServer();
        $router = $this->createRouter($server, [ 'close' ]);
        $router
            ->expects($this->once())
            ->method('close')
            ->with($conn, 404);

        $matcher = $this->createMatcher([ 'match' ]);
        $matcher
            ->expects($this->once())
            ->method('match')
            ->will($this->throwException(new ResourceNotFoundException));

        $router->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_ClosesConnectionWithCode500_WhenHttpRequestReceived_ButMatcherThrowsException()
    {
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $mssg = $this->createPassableMessage();

        $server = $this->createServer();
        $router = $this->createRouter($server, [ 'close' ]);
        $router
            ->expects($this->once())
            ->method('close')
            ->with($conn, 500);

        $matcher = $this->createMatcher([ 'match' ]);
        $matcher
            ->expects($this->once())
            ->method('match')
            ->will($this->throwException(new Exception));

        $router->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_HandlesError_WhenHttpRequestReceived_ButHandleConnectThrowsException()
    {
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $mssg = $this->createPassableMessage();

        $server = $this->createServer();
        $router = $this->createRouter($server);

        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleConnect')
            ->will($this->throwException($ex = new Exception));

        $component
            ->expects($this->once())
            ->method('handleError')
            ->with($conn, $ex);

        $matcher = $this->createMatcher([ 'match' ]);
        $matcher
            ->expects($this->once())
            ->method('match')
            ->will($this->returnValue([ '_controller' => $component ]));

        $router->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_HandlesConnectAndHandlesMessage_WhenHttpRequestReceived()
    {
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $mssg = $this->createPassableMessage();

        $server = $this->createServer();
        $router = $this->createRouter($server);

        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleConnect')
            ->with($conn);

        $component
            ->expects($this->once())
            ->method('handleMessage')
            ->with($conn, $mssg);

        $component
            ->expects($this->never())
            ->method('handleError');

        $matcher = $this->createMatcher([ 'match' ]);
        $matcher
            ->expects($this->once())
            ->method('match')
            ->will($this->returnValue([ '_controller' => $component ]));

        $router->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleError_ClosesConnectionWithCode500_WhenConnectionControllerDoesNotExist()
    {
        $ex   = new Exception();
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);

        $server = $this->createServer();
        $router = $this->createRouter($server, [ 'close' ]);
        $router
            ->expects($this->once())
            ->method('close')
            ->with($conn, 500);

        $router->handleError($conn, $ex);
    }

    /**
     *
     */
    public function testApiHandleError_ClosesConnectionWithCode500_WhenConnectionControllerDoesExistButThrowsException()
    {
        $ex   = new Exception();
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $conn->controller = $this->createComponent();
        $conn->controller
            ->expects($this->once())
            ->method('handleError')
            ->will($this->throwException(new Exception));

        $server = $this->createServer();
        $router = $this->createRouter($server, [ 'close' ]);
        $router
            ->expects($this->once())
            ->method('close')
            ->with($conn, 500);

        $router->handleError($conn, $ex);
    }

    /**
     *
     */
    public function testApiHandleError_PropagatesErrorToController_WhenConnectionControllerDoesExist()
    {
        $ex   = new Exception();
        $conn = $this->getMock(TransferConnection::class, [], [], '', false);
        $conn->controller = $this->createComponent();
        $conn->controller
            ->expects($this->once())
            ->method('handleError')
            ->with($conn, $ex);

        $server = $this->createServer();
        $router = $this->createRouter($server, [ 'close' ]);
        $router
            ->expects($this->never())
            ->method('close');

        $router->handleError($conn, $ex);
    }

    /**
     *
     */
    public function testProtectedApiClose_ClosesSocket()
    {
        $server = $this->createServer();
        $router = $this->createRouter($server);

        $code = 300;
        $conn = $this->getMock(TransferConnectionInterface::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(HttpResponseInterface::class));
        $conn
            ->expects($this->once())
            ->method('close');

        $this->callProtectedMethod($router, 'close', [ $conn, $code ]);
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
    public function createComponent()
    {
        return $this->getMock(ServerComponentInterface::class, [], [], '', false);
    }

    /**
     * @param string[]|null $methods
     * @return UrlMatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createMatcher($methods = [])
    {
        $matcher = $this->getMock(UrlMatcher::class, $methods, [
            $this->getProtectedProperty($this->router, 'routes'),
            $this->getProtectedProperty($this->router, 'context'),
        ]);

        $this->setProtectedProperty($this->router, 'matcher', $matcher);

        return $matcher;
    }

    /**
     * @param ServerComponentAwareInterface $aware
     * @param string[]|null $methods
     * @return HttpRouter|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRouter($aware, $methods = null)
    {
        $this->router = $this->getMock(HttpRouter::class, $methods, [ $aware ]);

        return $this->router;
    }

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createPassableMessage()
    {
        return new HttpRequest(
            'GET',
            '/route',
            [
                'Host'              => 'localhost:10080',
                'Connection'        => 'keep-alive',
                'Accept-Encoding'   => 'gzip, deflate',
                'Accept-Language'   => 'en-US, en',
                'Accept'            => 'text/html, application/xhtml+xml, application/xml',
            ],
            'Request Message'
        );
    }
}
