<?php

namespace Kraken\_Unit\Util\Invoker;

use Kraken\Test\TUnit;
use Kraken\Util\Invoker\Invoker;
use Kraken\Util\Invoker\InvokerInterface;

class InvokerTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $invoker = $this->createInvoker();

        $this->assertInstanceOf(Invoker::class, $invoker);
        $this->assertInstanceOf(InvokerInterface::class, $invoker);
    }

    /**
     *
     */
    public function testApiConstructor_PopulatesProxies()
    {
        $callable1 = function() {};
        $callable2 = function() {};
        $callables = [ 'func1' => $callable1, 'func2' => $callable2 ];

        $invoker = $this->createInvoker($callables);

        foreach ($callables as $func=>$callable)
        {
            $this->assertTrue($invoker->existsProxy($func));
        }
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $invoker = $this->createInvoker();
        unset($invoker);
    }

    /**
     *
     */
    public function testApiCall_CallsGlobalFunction_IfProxyDoesNotExist()
    {
        $invoker = $this->createInvoker();

        $result = rad2deg(M_PI_4);
        $this->assertSame($result, $invoker->call('rad2deg', [ M_PI_4 ]));
    }

    /**
     *
     */
    public function testApiCall_CallsGlobalFunction_IfProxyDoesExist()
    {
        $invoker = $this->createInvoker();
        $invoker->setProxy('rad2deg', function($value) {
            return $value;
        });

        $this->assertSame(M_PI_4, $invoker->call('rad2deg', [ M_PI_4 ]));
    }

    /**
     *
     */
    public function testApiExistsProxy_ReturnsTrue_WhenProxyDoesExist()
    {
        $invoker = $this->createInvoker();
        $invoker->setProxy('rad2deg', function($value) {
            return $value;
        });

        $this->assertTrue($invoker->existsProxy('rad2deg'));
    }

    /**
     *
     */
    public function testApiExistsProxy_ReturnsFalse_WhenProxyDoesNotExist()
    {
        $invoker = $this->createInvoker();

        $this->assertFalse($invoker->existsProxy('rad2deg'));
    }

    /**
     *
     */
    public function testApiSetProxy_SetsProxy()
    {
        $invoker = $this->createInvoker();

        $this->assertFalse($invoker->existsProxy('rad2deg'));
        $invoker->setProxy('rad2deg', function() {});
        $this->assertTrue($invoker->existsProxy('rad2deg'));
    }

    /**
     *
     */
    public function testApiRemoveProxy_RemovesProxy()
    {
        $invoker = $this->createInvoker();


        $invoker->setProxy('rad2deg', function() {});
        $this->assertTrue($invoker->existsProxy('rad2deg'));

        $invoker->removeProxy('rad2deg');
        $this->assertFalse($invoker->existsProxy('rad2deg'));
    }

    /**
     *
     */
    public function testApiGetProxy_ReturnsProxy()
    {
        $invoker = $this->createInvoker();
        $callable = function() {};

        $invoker->setProxy('rad2deg', $callable);
        $this->assertSame($callable, $invoker->getProxy('rad2deg'));
    }

    /**
     * @param callable[] $callables
     * @return Invoker
     */
    public function createInvoker($callables = [])
    {
        return new Invoker($callables);
    }
}
