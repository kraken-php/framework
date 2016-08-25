<?php

namespace Kraken\_Unit\Network\Websocket\Driver;

use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\Websocket\Driver\Version\HyBi10\Version  as  HyBi10Version;
use Kraken\Network\Websocket\Driver\Version\RFC6455\Version as RFC6455Version;
use Kraken\Network\Websocket\Driver\Version\VersionInterface;
use Kraken\Network\Websocket\Driver\Version\VersionManager;
use Kraken\Network\Websocket\Driver\Version\VersionManagerInterface;
use Kraken\Test\TUnit;

class VersionManagerTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $versioner = $this->createVersionManager();

        $this->assertInstanceOf(VersionManager::class, $versioner);
        $this->assertInstanceOf(VersionManagerInterface::class, $versioner);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $versioner = $this->createVersionManager();
        unset($versioner);
    }

    /**
     *
     */
    public function testApiGetVersion_ReturnsVersion_WhenVersionIsSetAndSupported()
    {
        $versioner = $this->createVersionManager();

        $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);
        $version = $this->getMock(VersionInterface::class, [], [], '', false);
        $version
            ->expects($this->once())
            ->method('isRequestSupported')
            ->with($req)
            ->will($this->returnValue(true));

        $versioner->enableVersion($version);

        $this->assertSame($version, $versioner->getVersion($req));
    }

    /**
     *
     */
    public function testApiGetVersion_ReturnsNull_WhenVersionIsSetButNotSupported()
    {
        $versioner = $this->createVersionManager();

        $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);
        $version = $this->getMock(VersionInterface::class, [], [], '', false);
        $version
            ->expects($this->once())
            ->method('isRequestSupported')
            ->with($req)
            ->will($this->returnValue(false));

        $versioner->enableVersion($version);

        $this->assertSame(null, $versioner->getVersion($req));
    }

    /**
     *
     */
    public function testApiGetVersion_ReturnsNull_WhenVersionIsNotSet()
    {
        $versioner = $this->createVersionManager();

        $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);

        $this->assertSame(null, $versioner->getVersion($req));
    }

    /**
     *
     */
    public function testApiCheckVersion_ReturnsTrue_WhenVersionIsEnabled()
    {
        $versioner = $this->createVersionManager();

        $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);
        $version = $this->getMock(VersionInterface::class, [], [], '', false);
        $version
            ->expects($this->once())
            ->method('isRequestSupported')
            ->with($req)
            ->will($this->returnValue(true));

        $versioner->enableVersion($version);

        $this->assertTrue($versioner->checkVersion($req));
    }

    /**
     *
     */
    public function testApiCheckVersion_ReturnsFalse_WhenVersionIsNotSupported()
    {
        $versioner = $this->createVersionManager();

        $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);

        $this->assertFalse($versioner->checkVersion($req));
    }

    /**
     *
     */
    public function testApiEnableVersion_EnablesVersion()
    {
        $versioner = $this->createVersionManager();

        $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);
        $version = $this->getMock(VersionInterface::class, [], [], '', false);
        $version
            ->expects($this->once())
            ->method('isRequestSupported')
            ->with($req)
            ->will($this->returnValue(true));

        $this->assertFalse($versioner->checkVersion($req));
        $versioner->enableVersion($version);
        $this->assertTrue($versioner->checkVersion($req));
    }

    /**
     *
     */
    public function testApiDisableVersion_DisablesVersion()
    {
        $versioner = $this->createVersionManager();

        $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);
        $version = $this->getMock(VersionInterface::class, [], [], '', false);
        $version
            ->expects($this->once())
            ->method('isRequestSupported')
            ->with($req)
            ->will($this->returnValue(true));

        $versioner->enableVersion($version);
        $this->assertTrue($versioner->checkVersion($req));

        $versioner->disableVersion($version);
        $this->assertFalse($versioner->checkVersion($req));
    }

    /**
     *
     */
    public function testApiGetVersionHeader_ReturnsVersionHeader()
    {
        $versioner = $this->createVersionManager();

        $versioner
            ->enableVersion(new RFC6455Version())
            ->enableVersion(new  HyBi10Version())
        ;

        $this->assertSame('13,6', $versioner->getVersionHeader());
    }

    /**
     * @return VersionManager
     */
    public function createVersionManager()
    {
        return new VersionManager();
    }
}
