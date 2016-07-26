<?php

namespace Kraken\_Unit\Util\Factory\_Partial;

use Kraken\_Unit\TestCase;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\IllegalFieldException;
use Kraken\Util\Factory\SimpleFactoryInterface;
use StdClass;

trait SimpleFactoryPartial
{
    /**
     * @return SimpleFactoryInterface
     */
    abstract public function createFactory();

    /**
     * @see TestCase::getTest
     * @return TestCase
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiBindParam_BindsParam()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';

        $factory->bindParam($key, null);

        $test->assertTrue($factory->hasParam($key));
    }

    /**
     *
     */
    public function testApiBindParam_ReturnsCaller()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';

        $test->assertSame($factory, $factory->bindParam($key, null));
    }

    /**
     *
     */
    public function testApiUnbindParam_UnbindsParam_ForExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';

        $factory->bindParam($key, null);
        $test->assertTrue($factory->hasParam($key));

        $factory->unbindParam($key);
        $test->assertFalse($factory->hasParam($key));
    }


    /**
     *
     */
    public function testApiUnbindParam_DoesNothing_ForNonExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';

        $factory->unbindParam($key);
        $test->assertFalse($factory->hasParam($key));
    }

    /**
     *
     */
    public function testApiUnbindParam_ReturnsCaller()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';

        $factory->unbindParam($key);
        $test->assertSame($factory, $factory->unbindParam($key));
    }

    /**
     *
     */
    public function testApiGetParam_ReturnsSameObject_ForExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';
        $std = new StdClass;

        $factory->bindParam($key, $std);
        $test->assertSame($std, $factory->getParam($key));
    }

    /**
     *
     */
    public function testApiGetParam_ThrowsException_ForNonExistingKey()
    {
        $test = $this->getTest();
        $test->setExpectedException(IllegalFieldException::class);

        $factory = $this->createFactory();
        $key = 'KEY';

        $factory->getParam($key);
    }

    /**
     *
     */
    public function testApiGetParam_ResolvesCallable()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';
        $val = new StdClass;

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->will($test->returnValue($val));

        $factory->bindParam($key, $mock);

        $test->assertSame($val, $factory->getParam($key));
    }

    /**
     *
     */
    public function testApiHasParam_ReturnsTrue_ForExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';
        $std = new StdClass;

        $factory->bindParam($key, $std);
        $test->assertTrue($factory->hasParam($key));
    }

    /**
     *
     */
    public function testApiHasParam_ReturnsFalse_ForNonExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';

        $test->assertFalse($factory->hasParam($key));
    }

    /**
     *
     */
    public function testApiGetParams_ReturnsEmptyArray_WhenNoParamsWereSet()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();

        $test->assertSame([], $factory->getParams());
    }

    /**
     *
     */
    public function testApiGetParams_ReturnsArray_WhenParamsWereSet()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();

        $factory->bindParam('A', $std1 = new StdClass);
        $factory->bindParam('B', $std2 = new StdClass);

        $test->assertSame([ 'A' => $std1, 'B' => $std2 ], $factory->getParams());
    }

    /**
     *
     */
    public function testApiDefine_AttachesFactoryMethod()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $callable = function() {};

        $factory->define($callable);

        $test->assertTrue($factory->hasDefinition());
    }

    /**
     *
     */
    public function testApiDefine_ReturnsCaller()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $callable = function() {};

        $test->assertSame($factory, $factory->define($callable));
    }

    /**
     *
     */
    public function testApiGetDefinition_ReturnsSameCallable_ForExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $callable = function() {};

        $factory->define($callable);
        $test->assertSame($callable, $factory->getDefinition());
    }

    /**
     *
     */
    public function testApiGetDefinition_ThrowsException_ForNonExistingKey()
    {
        $test = $this->getTest();
        $test->setExpectedException(IllegalFieldException::class);

        $factory = $this->createFactory();

        $factory->getDefinition();
    }

    /**
     *
     */
    public function testApiHasDefinition_ReturnsTrue_ForExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $callable = function() {};

        $factory->define($callable);
        $test->assertTrue($factory->hasDefinition());
    }

    /**
     *
     */
    public function testApiHasDefinition_ReturnsFalse_ForNonExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();

        $test->assertFalse($factory->hasDefinition());
    }

    /**
     *
     */
    public function testApiCreate_CallsAndReturnsFromDefinition()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $std = new StdClass;

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->will($test->returnCallback(function($val) {
                return $val;
            }));

        $factory->define($mock);

        $test->assertSame($std, $factory->create([ $std ]));
    }


    /**
     *
     */
    public function testApiCreate_ThrowsException_ForNonExistingKey()
    {
        $test = $this->getTest();
        $test->setExpectedException(IllegalCallException::class);

        $factory = $this->createFactory();

        $factory->create('NonExisting');
    }
}
