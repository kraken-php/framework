<?php

namespace Kraken\_Unit\Network\Socket\Component\Firewall;

use Dazzle\Socket\SocketListener;
use Kraken\Network\Null\NullServer;
use Kraken\Network\NetworkComponentAwareInterface;
use Kraken\Network\NetworkComponentInterface;
use Kraken\Network\Socket\Component\Firewall\SocketFirewall;
use Kraken\Network\Socket\Component\Firewall\SocketFirewallInterface;
use Kraken\Network\Socket\SocketServer;
use Kraken\Network\NetworkConnection;
use Kraken\Network\NetworkMessage;
use Kraken\Test\TUnit;
use Exception;

class FirewallTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        $this->assertInstanceOf(SocketFirewall::class, $firewall);
        $this->assertInstanceOf(SocketFirewallInterface::class, $firewall);
        $this->assertInstanceOf(NetworkComponentAwareInterface::class, $firewall);
        $this->assertInstanceOf(NetworkComponentInterface::class, $firewall);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        unset($firewall);
    }

    /**
     *
     */
    public function testApiSetComponent_SetsComponent_WhenComponentIsProvided()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        $firewall->setComponent($new = $this->createComponent());
        $this->assertSame($new, $firewall->getComponent());
    }

    /**
     *
     */
    public function testApiSetComponent_SetsNullComponent_WhenComponentIsNotProvided()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        $firewall->setComponent();
        $this->assertInstanceOf(NullServer::class, $firewall->getComponent());
    }

    /**
     *
     */
    public function testApiGetComponent_ReturnsComponent()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        $this->assertSame($component, $firewall->getComponent());
    }

    /**
     *
     */
    public function testApiBlockAddress_BlocksAddress()
    {
        $ip = '50.50.50.50';

        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        $this->assertFalse(array_key_exists($ip, $this->getProtectedProperty($firewall, 'blacklist')));
        $firewall->blockAddress($ip);
        $this->assertTrue(array_key_exists($ip, $this->getProtectedProperty($firewall, 'blacklist')));
    }

    /**
     *
     */
    public function testApiUnblockAddress_UnblocksAddress()
    {
        $ip = '50.50.50.50';

        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        $firewall->blockAddress($ip);
        $this->assertTrue(array_key_exists($ip, $this->getProtectedProperty($firewall, 'blacklist')));

        $firewall->unblockAddress($ip);
        $this->assertFalse(array_key_exists($ip, $this->getProtectedProperty($firewall, 'blacklist')));
    }

    /**
     *
     */
    public function testApiIsBlocked_ReturnsFalse_WhenIpIsNotBlocked()
    {
        $ip = '50.50.50.50';

        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        $firewall->blockAddress($ip);
        $firewall->unblockAddress($ip);

        $this->assertFalse($firewall->isAddressBlocked($ip));
    }

    /**
     *
     */
    public function testApiIsBlocked_ReturnsTrue_WhenIpIsBlocked()
    {
        $ip = '50.50.50.50';

        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        $firewall->blockAddress($ip);
        $this->assertTrue($firewall->isAddressBlocked($ip));
    }

    /**
     *
     */
    public function testApiGetBlockedAddresses()
    {
        $server    = $this->createServer();
        $component = $this->createComponent();
        $firewall  = $this->createFirewall($server, $component);

        $firewall->blockAddress($ip1 = '50.25.25.25');
        $firewall->blockAddress($ip2 = '50.50.50.50');

        $this->assertSame([ $ip1, $ip2 ], $firewall->getBlockedAddresses());
    }

    /**
     *
     */
    public function testApiHandleConnect_PropagatesConnect_WhenIPIsValid()
    {
        $host = 'host';
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue($host));

        $server    = $this->createServer();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleConnect')
            ->with($conn);

        $firewall  = $this->createFirewall($server, $component, [ 'isAddressBlocked' ]);
        $firewall
            ->expects($this->once())
            ->method('isAddressBlocked')
            ->with($host)
            ->will($this->returnValue(false));

        $firewall->handleConnect($conn);
    }

    /**
     *
     */
    public function testApiHandleConnect_ClosesConnection_WhenIPIsNotValid()
    {
        $host = 'host';
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue($host));
        $conn
            ->expects($this->once())
            ->method('close');

        $server    = $this->createServer();
        $component = $this->createComponent();
        $component
            ->expects($this->never())
            ->method('handleConnect');

        $firewall  = $this->createFirewall($server, $component, [ 'isAddressBlocked' ]);
        $firewall
            ->expects($this->once())
            ->method('isAddressBlocked')
            ->with($host)
            ->will($this->returnValue(true));

        $firewall->handleConnect($conn);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_PropagatesDisconnect_WhenIPIsValid()
    {
        $host = 'host';
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue($host));

        $server    = $this->createServer();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleDisconnect')
            ->with($conn);

        $firewall  = $this->createFirewall($server, $component, [ 'isAddressBlocked' ]);
        $firewall
            ->expects($this->once())
            ->method('isAddressBlocked')
            ->with($host)
            ->will($this->returnValue(false));

        $firewall->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_DoesNothing_WhenIPIsNotValid()
    {
        $host = 'host';
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue($host));

        $server    = $this->createServer();
        $component = $this->createComponent();
        $component
            ->expects($this->never())
            ->method('handleDisconnect');

        $firewall  = $this->createFirewall($server, $component, [ 'isAddressBlocked' ]);
        $firewall
            ->expects($this->once())
            ->method('isAddressBlocked')
            ->with($host)
            ->will($this->returnValue(true));

        $firewall->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleMessage_PropagatesMessage()
    {
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);
        $mssg = $this->getMock(NetworkMessage::class, [], [], '', false);

        $server    = $this->createServer();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleMessage')
            ->with($conn, $mssg);

        $firewall  = $this->createFirewall($server, $component);

        $firewall->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleError_PropagatesError_WhenIPIsValid()
    {
        $host = 'host';
        $ex   = new Exception();
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue($host));

        $server    = $this->createServer();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleError')
            ->with($conn, $ex);

        $firewall  = $this->createFirewall($server, $component, [ 'isAddressBlocked' ]);
        $firewall
            ->expects($this->once())
            ->method('isAddressBlocked')
            ->with($host)
            ->will($this->returnValue(false));

        $firewall->handleError($conn, $ex);
    }

    /**
     *
     */
    public function testApiHandleError_DoesNothing_WhenIPIsNotValid()
    {
        $host = 'host';
        $ex   = new Exception();
        $conn = $this->getMock(NetworkConnection::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue($host));

        $server    = $this->createServer();
        $component = $this->createComponent();
        $component
            ->expects($this->never())
            ->method('handleError');

        $firewall  = $this->createFirewall($server, $component, [ 'isAddressBlocked' ]);
        $firewall
            ->expects($this->once())
            ->method('isAddressBlocked')
            ->with($host)
            ->will($this->returnValue(true));

        $firewall->handleError($conn, $ex);
    }

    /**
     * @param string[]|null $methods
     * @return NetworkComponentAwareInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createServer($methods = [])
    {
        $listener = $this->getMock(SocketListener::class, [], [], '', false);

        return $this->getMock(SocketServer::class, $methods, [ $listener ]);
    }

    /**
     * @return NetworkComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createComponent()
    {
        return $this->getMock(NetworkComponentInterface::class, [], [], '', false);
    }

    /**
     * @param NetworkComponentAwareInterface $aware
     * @param NetworkComponentInterface $component
     * @param string[]|null $methods
     * @return SocketFirewall|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createFirewall($aware, $component, $methods = null)
    {
        return $this->getMock(SocketFirewall::class, $methods, [ $aware, $component ]);
    }
}
