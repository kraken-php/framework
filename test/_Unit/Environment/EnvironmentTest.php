<?php

namespace Kraken\_Unit\Environment;

use Kraken\_Unit\Environment\_Mock\EnvironmentMock;
use Kraken\Runtime\RuntimeContextInterface;
use Kraken\Environment\Environment;
use Kraken\Environment\EnvironmentInterface;
use Kraken\Environment\Loader\Loader;
use Kraken\Test\TUnit;
use Kraken\Util\Invoker\Invoker;

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
    public function testApiRemoveOption_CallsPHPFunction()
    {
        $env = $this->createEnvironment();

        $env->removeOption($key = 'key');

        $this->assertSame(
            [ 'ini_restore' => [ $key ] ],
            $env->calls
        );
    }

    /**
     *
     */
    public function testApiSetEnv_SetsEnvironmentVariable()
    {
        $env = $this->createEnvironment();
        $env->setEnv('KEY', 'VALUE');

        $this->assertSame('VALUE', getenv('KEY'));
    }

    /**
     *
     */
    public function testApiGetEnv_ReturnsEnvironmentVariable()
    {
        $env = $this->createEnvironment();
        putenv('KEY=VALUE');

        $this->assertSame(getenv('KEY'), $env->getEnv('KEY'));
    }

    /**
     *
     */
    public function testApiRemoveEnv_RemovesEnvironmentVariable()
    {
        $env = $this->createEnvironment();

        $env->setEnv('KEY', 'VALUE');
        $this->assertSame('VALUE', getenv('KEY'));

        $env->removeEnv('KEY');
        $this->assertSame(false, getenv('KEY'));
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
     *
     */
    public function testProtectedApiCreateInvoker_CreatesInvoker()
    {
        $env = $this->createEnvironment();

        $this->assertInstanceOf(Invoker::class, $this->callProtectedMethod($env, 'createInvoker'));
    }

    /**
     *
     */
    public function testProtectedApiCreateLoader_CreatesLoader()
    {
        $env = $this->createEnvironment();

        $this->assertInstanceOf(Loader::class, $this->callProtectedMethod($env, 'createLoader', [ '', false ]));
    }


    /**
     * @return EnvironmentMock
     */
    public function createEnvironment()
    {
        $context = $this->getMock(RuntimeContextInterface::class);

        return new EnvironmentMock($context, '');
    }
}
