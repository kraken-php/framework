<?php

namespace Kraken\_Unit\Util\Factory\_Partial;

use Kraken\_Unit\TestCase;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\IllegalFieldException;
use Kraken\Util\Factory\FactoryInterface;
use StdClass;

trait FactoryPartial
{
    /**
     * @return FactoryInterface
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
        $key = 'KEY';
        $callable = function() {};

        $factory->define($key, $callable);

        $test->assertTrue($factory->hasDefinition($key));
    }

    /**
     *
     */
    public function testApiDefine_ReturnsCaller()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';
        $callable = function() {};

        $test->assertSame($factory, $factory->define($key, $callable));
    }

    /**
     *
     */
    public function testApiRemove_RemovesFactoryMethod_ForExistingMethod()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';
        $callable = function() {};

        $factory->define($key, $callable);
        $test->assertTrue($factory->hasDefinition($key));

        $factory->remove($key);
        $test->assertFalse($factory->hasDefinition($key));
    }

    /**
     *
     */
    public function testApiRemove_DoesNothing_ForNonExistingMethod()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';

        $factory->remove($key);
        $test->assertFalse($factory->hasDefinition($key));
    }

    /**
     *
     */
    public function testApiRemove_ReturnsCaller()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';

        $test->assertSame($factory, $factory->remove($key));
    }

    /**
     *
     */
    public function testApiGetDefinition_ReturnsSameCallable_ForExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';
        $callable = function() {};

        $factory->define($key, $callable);
        $test->assertSame($callable, $factory->getDefinition($key));
    }

    /**
     *
     */
    public function testApiGetDefinition_ThrowsException_ForNonExistingKey()
    {
        $test = $this->getTest();
        $test->setExpectedException(IllegalFieldException::class);

        $factory = $this->createFactory();
        $key = 'KEY';

        $factory->getDefinition($key);
    }

    /**
     *
     */
    public function testApiHasDefinition_ReturnsTrue_ForExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';
        $callable = function() {};

        $factory->define($key, $callable);
        $test->assertTrue($factory->hasDefinition($key));
    }

    /**
     *
     */
    public function testApiHasDefinition_ReturnsFalse_ForNonExistingKey()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';

        $test->assertFalse($factory->hasDefinition($key));
    }

    /**
     *
     */
    public function testApiGetDefinitions_ReturnsEmptyArray_WhenNoDefinitionsWereSet()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();

        $test->assertSame([], $factory->getDefinitions());
    }

    /**
     *
     */
    public function testApiGetDefinitions_ReturnsArray_WhenParamsWereSet()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();

        $factory->define('A', $c1 = $test->createCallableMock());
        $factory->define('B', $c2 = $test->createCallableMock());

        $test->assertSame([ 'A' => $c1, 'B' => $c2 ], $factory->getDefinitions());
    }

    /**
     *
     */
    public function testApiCreate_CallsAndReturnsFromDefinition()
    {
        $test = $this->getTest();
        $factory = $this->createFactory();
        $key = 'KEY';
        $std = new StdClass;

        $mock = $test->createCallableMock();
        $mock
            ->expects($test->once())
            ->method('__invoke')
            ->will($test->returnCallback(function($val) {
                return $val;
            }));

        $factory->define($key, $mock);

        $test->assertSame($std, $factory->create($key, [ $std ]));
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
