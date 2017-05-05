<?php

namespace Kraken\_Unit\SSH;

use Kraken\SSH\SSH2Config;
use Kraken\Test\TUnit;

class SSH2ConfigTest extends TUnit
{
    /**
     *
     */
    public function testConstructor_SetsDefaultParams()
    {
        $config = new SSH2Config();

        $this->assertAttributeEquals('localhost', 'host', $config);
        $this->assertAttributeEquals(22, 'port', $config);
        $this->assertAttributeEquals([], 'methods', $config);
    }

    /**
     *
     */
    public function testConstructor_UsesPassedArguments()
    {
        $config = new SSH2Config($host = 'A', $port = 50, $methods = [ 'method' => 'option' ]);

        $this->assertAttributeEquals($host, 'host', $config);
        $this->assertAttributeEquals($port, 'port', $config);
        $this->assertAttributeEquals($methods, 'methods', $config);
    }

    /**
     *
     */
    public function testDestructor_DoesNotThrowThrowable()
    {
        $config = new SSH2Config();
        unset($config);
    }

    /**
     *
     */
    public function testApiGetHost_ReturnsHost()
    {
        $config = new SSH2Config($host = 'A');
        $this->assertSame($host, $config->getHost());
    }

    /**
     *
     */
    public function testApiGetPort_ReturnsPort()
    {
        $config = new SSH2Config('A', $port = 50);
        $this->assertSame($port, $config->getPort());
    }

    /**
     *
     */
    public function testApiGetMethods_ReturnsMethods()
    {
        $config = new SSH2Config('A', 50, $methods = [ 'something' => 'value' ]);
        $this->assertSame($methods, $config->getMethods());
    }
}
