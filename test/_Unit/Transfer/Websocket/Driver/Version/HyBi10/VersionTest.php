<?php

namespace Kraken\_Unit\Transfer\Websocket\Driver\HyBi10;

use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\Websocket\Driver\Version\HyBi10\Version;
use Kraken\Transfer\Websocket\Driver\Version\VersionInterface;
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
        $protocols = [ 5, 6, 12, 13, 15 ];
        $expects   = [ false, true, true, false, false ];

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

        $this->assertSame(6, $version->getVersionNumber());
    }

    /**
     * @return Version
     */
    public function createVersion()
    {
        return new Version();
    }
}
