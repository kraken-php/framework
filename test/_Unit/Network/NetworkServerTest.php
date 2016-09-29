<?php

namespace Kraken\_Unit\Network;

use Kraken\Ipc\Socket\SocketListener;
use Kraken\Loop\Loop;
use Kraken\Network\Http\Component\Router\HttpRouter;
use Kraken\Network\NetworkComponentInterface;
use Kraken\Network\Socket\Component\Firewall\SocketFirewall;
use Kraken\Network\NetworkServer;
use Kraken\Network\NetworkServerInterface;
use Kraken\Test\TUnit;

class NetworkServerTest extends TUnit
{
    /**
     * @var NetworkServer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $server;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $server = $this->createNetworkServer();

        $this->assertInstanceOf(NetworkServer::class, $server);
        $this->assertInstanceOf(NetworkServerInterface::class, $server);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $server = $this->createNetworkServer();
        unset($server);
    }

    /**
     *
     */
    public function testApiExistsRoute_CallsMethodOnRouter()
    {
        $path = 'path';
        $result = true;

        $server = $this->createNetworkServer();
        $router = $this->createRouter([ 'existsRoute' ]);
        $router
            ->expects($this->once())
            ->method('existsRoute')
            ->with($path)
            ->will($this->returnValue($result));

        $this->assertSame($result, $server->existsRoute($path));
    }

    /**
     *
     */
    public function testApiAddRoute_CallsMethodOnRouter()
    {
        $path = 'path';
        $component = $this->getMock(NetworkComponentInterface::class, [], [], '', false);

        $server = $this->createNetworkServer();
        $router = $this->createRouter([ 'addRoute' ]);
        $router
            ->expects($this->once())
            ->method('addRoute')
            ->with($path, $component)
            ->will($this->returnValue($router));

        $this->assertSame($router, $server->addRoute($path, $component));
    }

    /**
     *
     */
    public function testApiRemoveRoute_CallsMethodOnRouter()
    {
        $path = 'path';

        $server = $this->createNetworkServer();
        $router = $this->createRouter([ 'removeRoute' ]);
        $router
            ->expects($this->once())
            ->method('removeRoute')
            ->with($path)
            ->will($this->returnValue($router));

        $this->assertSame($router, $server->removeRoute($path));
    }

    /**
     *
     */
    public function testApiStop_CallsMethodOnListener()
    {
        $server = $this->createNetworkServer();
        $router = $this->createListener([ 'close' ]);
        $router
            ->expects($this->once())
            ->method('close');

        $server->stop();
    }

    /**
     *
     */
    public function testApiClose_CallsMethodOnListener()
    {
        $server = $this->createNetworkServer();
        $router = $this->createListener([ 'close' ]);
        $router
            ->expects($this->once())
            ->method('close');

        $server->close();
    }

    /**
     *
     */
    public function testApiBlockAddress_CallsMethodOnFirewall()
    {
        $ip = '50.50.50.50';

        $server   = $this->createNetworkServer();
        $firewall = $this->createFirewall([ 'blockAddress' ]);
        $firewall
            ->expects($this->once())
            ->method('blockAddress')
            ->with($ip);

        $this->assertSame($server, $server->blockAddress($ip));
    }

    /**
     *
     */
    public function testApiUnblockAddress_CallsMethodOnFirewall()
    {
        $ip = '50.50.50.50';

        $server   = $this->createNetworkServer();
        $firewall = $this->createFirewall([ 'unblockAddress' ]);
        $firewall
            ->expects($this->once())
            ->method('unblockAddress')
            ->with($ip);

        $this->assertSame($server, $server->unblockAddress($ip));
    }

    /**
     *
     */
    public function testApiIsAddressBlocked_ReturnsFalse_WhenFirewallDoesNotExist()
    {
        $ip = '50.50.50.50';

        $server = $this->createNetworkServer();

        $this->assertSame(false, $server->isAddressBlocked($ip));
    }

    /**
     *
     */
    public function testApiIsAddressBlocked_CallsMethodOnFirewall_WhenFirewallDoesExist()
    {
        $ip = '50.50.50.50';
        $result = 'result';

        $server   = $this->createNetworkServer();
        $firewall = $this->createFirewall([ 'isAddressBlocked' ]);
        $firewall
            ->expects($this->once())
            ->method('isAddressBlocked')
            ->with($ip)
            ->will($this->returnValue($result));

        $this->assertSame($result, $server->isAddressBlocked($ip));
    }

    /**
     *
     */
    public function testApiGetBlockedAddresses_ReturnsEmptyArray_WhenFirewallDoesNotExist()
    {
        $server = $this->createNetworkServer();
        $this->assertSame([], $server->getBlockedAddresses());
    }

    /**
     *
     */
    public function testApiGetBlockedAddresses_CallsMethodOnFirewall_WhenFirewallDoesExist()
    {
        $ips = [ '50.25.25.25', '50.50.50.50' ];

        $server   = $this->createNetworkServer();
        $firewall = $this->createFirewall([ 'getBlockedAddresses' ]);
        $firewall
            ->expects($this->once())
            ->method('getBlockedAddresses')
            ->will($this->returnValue($ips));

        $this->assertSame($ips, $server->getBlockedAddresses());
    }

    /**
     *
     */
    public function testApiSetLoop_CallsMethodOnListener()
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);

        $server = $this->createNetworkServer();
        $router = $this->createListener([ 'setLoop' ]);
        $router
            ->expects($this->once())
            ->method('setLoop')
            ->with($loop);

        $server->setLoop($loop);
    }

    /**
     *
     */
    public function testApiGetLoop_CallsMethodOnListener()
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);

        $server = $this->createNetworkServer();
        $router = $this->createListener([ 'getLoop' ]);
        $router
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));

        $this->assertSame($loop, $server->getLoop());
    }

    /**
     *
     */
    public function testApiIsPaused_CallsMethodOnListener()
    {
        $result = true;

        $server = $this->createNetworkServer();
        $router = $this->createListener([ 'isPaused' ]);
        $router
            ->expects($this->once())
            ->method('isPaused')
            ->will($this->returnValue($result));

        $this->assertSame($result, $server->isPaused());
    }

    /**
     *
     */
    public function testApiPause_CallsMethodOnListener()
    {
        $server = $this->createNetworkServer();
        $router = $this->createListener([ 'pause' ]);
        $router
            ->expects($this->once())
            ->method('pause');

        $server->pause();
    }

    /**
     *
     */
    public function testApiResume_CallsMethodOnListener()
    {
        $server = $this->createNetworkServer();
        $router = $this->createListener([ 'resume' ]);
        $router
            ->expects($this->once())
            ->method('resume');

        $server->resume();
    }

    /**
     *
     */
    public function testApiCreateFirewall_CreatesFirewall()
    {
        $server = $this->createNetworkServer();

        $this->assertSame(null, $this->getProtectedProperty($server, 'firewall'));
        $this->callProtectedMethod($server, 'createFirewall');
        $this->assertInstanceOf(SocketFirewall::class, $this->getProtectedProperty($server, 'firewall'));
    }

    /**
     * @param string[]|null $methods
     * @return SocketListener|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createListener($methods = null)
    {
        $listener = $this->getMock(SocketListener::class, $methods, [], '', false);

        $this->setProtectedProperty($this->server, 'listener', $listener);

        return $listener;
    }

    /**
     * @param string[]|null $methods
     * @return HttpRouter|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRouter($methods = null)
    {
        $router = $this->getMock(HttpRouter::class, $methods, [], '', false);

        $this->setProtectedProperty($this->server, 'router', $router);

        return $router;
    }

    /**
     * @param string[]|null $methods
     * @return HttpRouter|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createFirewall($methods = null)
    {
        $firewall = $this->getMock(SocketFirewall::class, $methods, [], '', false);

        $this->setProtectedProperty($this->server, 'firewall', $firewall);

        return $firewall;
    }

    /**
     * @param string[]|null $methods
     * @return NetworkServer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createNetworkServer($methods = null)
    {
        $listener = $this->getMock(SocketListener::class, [], [], '', false);

        $this->server = $this->getMock(NetworkServer::class, $methods, [ $listener ]);

        return $this->server;
    }
}
