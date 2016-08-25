<?php

namespace Kraken\_Unit\Network\Websocket\Driver\RFC6455;

use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\Websocket\Driver\Version\RFC6455\Version;
use Kraken\Network\Websocket\Driver\Version\VersionInterface;
use Kraken\Test\TUnit;

class VersionTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $version = $this->createVersion();

        $this->assertInstanceOf(Version::class, $version);
        $this->assertInstanceOf(VersionInterface::class, $version);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $version = $this->createVersion();
        unset($version);
    }

    /**
     *
     */
    public function testApiIsRequestSupported_ReturnsBoolForDifferentVersions()
    {
        $version = $this->createVersion();
        $protocols = [ 12, 13, 14 ];
        $expects   = [ false, true, false ];

        foreach ($protocols as $index=>$protocol)
        {
            $expect = $expects[$index];
            $req = $this->getMock(HttpRequestInterface::class, [], [], '', false);
            $req
                ->expects($this->once())
                ->method('getHeaderLine')
                ->with('Sec-WebSocket-Version')
                ->will($this->returnValue($protocol));

            $this->assertSame($expect, $version->isRequestSupported($req));
        }
    }

    /**
     *
     */
    public function testApiGetVersionNumber_ReturnsCorrectVersionNumber()
    {
        $version = $this->createVersion();

        $this->assertSame(13, $version->getVersionNumber());
    }

    /**
     * @return Version
     */
    public function createVersion()
    {
        return new Version();
    }
}
