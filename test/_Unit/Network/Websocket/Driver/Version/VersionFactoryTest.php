<?php

namespace Kraken\_Unit\Network\Websocket\Driver;

use Kraken\Network\Websocket\Driver\Version\HyBi10\Version  as  HyBi10Version;
use Kraken\Network\Websocket\Driver\Version\RFC6455\Version as RFC6455Version;
use Kraken\Network\Websocket\Driver\Version\VersionFactory;
use Kraken\Network\Websocket\Driver\Version\VersionFactoryInterface;
use Kraken\Test\TUnit;

class VersionFactoryTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $versioner = $this->createVersionFactory();

        $this->assertInstanceOf(VersionFactory::class, $versioner);
        $this->assertInstanceOf(VersionFactoryInterface::class, $versioner);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $versioner = $this->createVersionFactory();
        unset($versioner);
    }

    /**
     *
     */
    public function testCaseFactory_PossesDefinitions()
    {
        $versioner = $this->createVersionFactory();

        $this->assertInstanceOf( HyBi10Version::class, $versioner->create('HyBi10'));
        $this->assertInstanceOf(RFC6455Version::class, $versioner->create('RFC6455'));
    }

    /**
     * @return VersionFactory
     */
    public function createVersionFactory()
    {
        return new VersionFactory();
    }
}
