<?php

namespace Kraken\_Unit\Transfer\Websocket\Driver;

use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\Websocket\Driver\Version\VersionInterface;
use Kraken\Transfer\Websocket\Driver\Version\VersionManager;
use Kraken\Transfer\Websocket\Driver\WsDriver;
use Kraken\Transfer\Websocket\Driver\WsDriverInterface;
use Kraken\Test\TUnit;

class WsDriverTest extends TUnit
{
    /**
     * @var WsDriver
     */
    private $driver;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $ws = $this->createWsDriver();

        $this->assertInstanceOf(WsDriver::class, $ws);
        $this->assertInstanceOf(WsDriverInterface::class, $ws);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $ws = $this->createWsDriver();
        unset($ws);
    }

    /**
     *
     */
    public function testApiSetEncodingChecks_SetsEncodingCheck()
    {
        $ws = $this->createWsDriver();

        $ws->setEncodingChecks(true);
        $this->assertTrue($this->getProtectedProperty($ws, 'validator')->on);
    }

    /**
     *
     */
    public function testApiSetEncodingChecks_UnsetsEncodingCheck()
    {
        $ws = $this->createWsDriver();

        $ws->setEncodingChecks(false);
        $this->assertFalse($this->getProtectedProperty($ws, 'validator')->on);
    }

    /**
     *
     */
    public function testApiGetVersion_CallsMethodOnVersioner()
    {
        $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);
        $result = 'result';

        $ws = $this->createWsDriver();
        $versioner = $this->createVersioner();
        $versioner
            ->expects($this->once())
            ->method('getVersion')
            ->with($req)
            ->will($this->returnValue($result));

        $this->assertSame($result, $ws->getVersion($req));
    }

    /**
     *
     */
    public function testApiCheckVersion_CallsMethodOnVersioner()
    {
        $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);
        $result = 'result';

        $ws = $this->createWsDriver();
        $versioner = $this->createVersioner();
        $versioner
            ->expects($this->once())
            ->method('checkVersion')
            ->with($req)
            ->will($this->returnValue($result));

        $this->assertSame($result, $ws->checkVersion($req));
    }

    /**
     *
     */
    public function testApiEnableVersion_CallsMethodOnVersioner()
    {
        $req = $this->getMock(VersionInterface::class, [], [], '', false);
        $result = 'result';

        $ws = $this->createWsDriver();
        $versioner = $this->createVersioner();
        $versioner
            ->expects($this->once())
            ->method('enableVersion')
            ->with($req)
            ->will($this->returnValue($result));

        $this->assertSame($result, $ws->enableVersion($req));
    }

    /**
     *
     */
    public function testApiDisableVersion_CallsMethodOnVersioner()
    {
        $req = $this->getMock(VersionInterface::class, [], [], '', false);
        $result = 'result';

        $ws = $this->createWsDriver();
        $versioner = $this->createVersioner();
        $versioner
            ->expects($this->once())
            ->method('disableVersion')
            ->with($req)
            ->will($this->returnValue($result));

        $this->assertSame($result, $ws->disableVersion($req));
    }

    /**
     *
     */
    public function testApiGetVersionHeader_CallsMethodOnVersioner()
    {
        $result = 'result';

        $ws = $this->createWsDriver();
        $versioner = $this->createVersioner();
        $versioner
            ->expects($this->once())
            ->method('getVersionHeader')
            ->will($this->returnValue($result));

        $this->assertSame($result, $ws->getVersionHeader());
    }

    /**
     * @param string[]|null $methods
     * @return VersionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createVersioner($methods = [])
    {
        $versioner = $this->getMock(VersionManager::class, $methods, [], '', false);

        $this->setProtectedProperty($this->driver, 'versioner', $versioner);

        return $versioner;
    }

    /**
     * @return WsDriver
     */
    public function createWsDriver()
    {
        $this->driver = new WsDriver();

        return $this->driver;
    }
}
