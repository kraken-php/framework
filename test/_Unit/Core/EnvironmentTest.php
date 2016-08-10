<?php

namespace Kraken\_Unit\Core;

use Kraken\_Unit\Core\_Mock\EnvironmentMock;
use Kraken\Config\ConfigInterface;
use Kraken\Core\CoreInputContextInterface;
use Kraken\Core\Environment;
use Kraken\Core\EnvironmentInterface;
use Kraken\Test\TUnit;

class EnvironmentTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $env = $this->createEnvironment();

        $this->assertInstanceOf(Environment::class, $env);
        $this->assertInstanceOf(EnvironmentInterface::class, $env);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $env = $this->createEnvironment();
        unset($env);
    }

    /**
     *
     */
    public function testApiSetOption_CallsPHPFunction()
    {
        $env = $this->createEnvironment();

        $env->setOption($key = 'key', $val = 'val');

        $this->assertSame(
            [ 'ini_set' => [ $key, $val ] ],
            $env->calls
        );
    }

    /**
     *
     */
    public function testApiGetOption_CallsPHPFunction()
    {
        $env = $this->createEnvironment();

        $env->getOption($key = 'key');

        $this->assertSame(
            [ 'ini_get' => [ $key ] ],
            $env->calls
        );
    }

    /**
     *
     */
    public function testApiRestoreOption_CallsPHPFunction()
    {
        $env = $this->createEnvironment();

        $env->restoreOption($key = 'key');

        $this->assertSame(
            [ 'ini_restore' => [ $key ], 'ini_get' => [ $key ] ],
            $env->calls
        );
    }

    /**
     *
     */
    public function testApiGetEnv_ReturnsEnvironmentVariable()
    {
        $env = $this->createEnvironment();

        $this->assertSame('secret', $env->getEnv($key = 'key'));
    }

    /**
     *
     */
    public function testApiMatchEnv_ReturnsTrue_WhenValueMatched()
    {
        $env = $this->createEnvironment();

        $this->assertTrue($env->matchEnv($key = 'key', $val = 'secret'));
    }

    /**
     *
     */
    public function testApiMatchEnv_ReturnsFalse_WhenValueNotMatched()
    {
        $env = $this->createEnvironment();

        $this->assertFalse($env->matchEnv($key = 'key', $val = 'other'));
    }

    /**
     *
     */
    public function testApiRegisterErrorHandler_CallsPHPFunction()
    {
        $env = $this->createEnvironment();

        $env->registerErrorHandler($callable = function() {});

        $this->assertSame(
            [ 'set_error_handler' => [ $callable ] ],
            $env->calls
        );
    }

    /**
     *
     */
    public function testApiRegisterShutdownHandler_CallsPHPFunction()
    {
        $env = $this->createEnvironment();

        $env->registerShutdownHandler($callable = function() {});

        $this->assertTrue(array_key_exists('register_shutdown_function', $env->calls));
    }

    /**
     *
     */
    public function testApiRegisterExceptionHandler_CallsPHPFunction()
    {
        $env = $this->createEnvironment();

        $env->registerExceptionHandler($callable = function() {});

        $this->assertSame(
            [ 'set_exception_handler' => [ $callable ] ],
            $env->calls
        );
    }

    /**
     *
     */
    public function testApiRegisterTerminationHandler_CallsPHPFunction()
    {
        $env = $this->createEnvironment();

        $env->registerTerminationHandler($callable = function() {});

        $this->assertSame(
            [ 'pcntl_signal' => [ Environment::SIGTERM, $callable ] ],
            $env->calls
        );
    }


    /**
     * @return EnvironmentMock
     */
    public function createEnvironment()
    {
        $context = $this->getMock(CoreInputContextInterface::class);

        $config = $this->getMock(ConfigInterface::class);
        $config
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($key) {
                return 'secret';
            }));

        return new EnvironmentMock($context, $config);
    }
}
