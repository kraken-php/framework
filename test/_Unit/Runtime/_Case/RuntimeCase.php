<?php

namespace Kraken\_Unit\Runtime\_Case;

use Kraken\Core\Core;
use Kraken\Event\EventEmitter;
use Kraken\Event\EventListener;
use Kraken\Loop\Loop;
use Kraken\Promise\Promise;
use Kraken\Runtime\RuntimeContainer;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Runtime\RuntimeManager;
use Kraken\Runtime\RuntimeModel;
use Kraken\Test\TUnit;
use Exception;

trait RuntimeCase
{
    /**
     * @return TUnit
     */
    abstract public function getTest();

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $test = $this->getTest();
        $runtime = $this->createRuntime();


        $test->assertInstanceOf(RuntimeContainer::class, $runtime);
        $test->assertInstanceOf(RuntimeContainerInterface::class, $runtime);
        $test->assertInstanceOf(EventEmitter::class, $runtime);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $test = $this->getTest();
        $runtime = $this->createRuntime();
        unset($runtime);
    }

    /**
     *
     */
    public function testApiGetModel_ReturnsModel()
    {
        $test = $this->getTest();
        $runtime = $this->createRuntime();

        $test->assertInstanceOf(RuntimeModel::class, $runtime->getModel());
    }

    /**
     *
     */
    public function testApiGetType_ReturnsType()
    {
        $test = $this->getTest();

        $result = 'RuntimeType';
        $mock = $test->getMock(Core::class, [ 'getType' ], [], '', false);
        $mock
            ->expects($test->once())
            ->method('getType')
            ->will($test->returnValue($result));

        $runtime = $this->createRuntime([], [ 'getCore' ]);
        $runtime
            ->expects($test->once())
            ->method('getCore')
            ->will($test->returnValue($mock));

        $test->assertSame($result, $runtime->getType());
    }

    /**
     *
     */
    public function testApiGetParent_ReturnsParent()
    {
        $test = $this->getTest();
        $runtime = $this->createRuntime([ $parent = 'someParent' ]);

        $test->assertSame($parent, $runtime->getParent());
    }

    /**
     *
     */
    public function testApiGetAlias_ReturnsAlias()
    {
        $test = $this->getTest();
        $runtime = $this->createRuntime([ 'parent', $alias = 'someAlias' ]);

        $test->assertSame($alias, $runtime->getAlias());
    }

    /**
     *
     */
    public function testApiGetName_ReturnsName()
    {
        $test = $this->getTest();
        $runtime = $this->createRuntime([ 'parent', 'alias', $name = 'someName' ]);

        $test->assertSame($name, $runtime->getName());
    }

    /**
     *
     */
    public function testApiGetArgs_ReturnsArgs()
    {
        $test = $this->getTest();
        $args = [ 'arg1' => 'val1', 'arg2' => 'val2' ];
        $runtime = $this->createRuntime([ 'parent', 'alias', 'name', $args ]);

        $test->assertSame($args, $runtime->getArgs());
    }

    /**
     *
     */
    public function testApiGetCore_CallsModelMethod()
    {
        $test = $this->getTest();

        $core  = $test->getMock(Core::class,[], [], '', false);
        $model = $test->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($test->once())
            ->method('getCore')
            ->will($test->returnValue($core));

        $runtime = $this->createRuntime();
        $test->setProtectedProperty($runtime, 'model', $model);

        $test->assertSame($core, $runtime->getCore());
    }

    /**
     *
     */
    public function testApiSetCore_CallsModelMethod()
    {
        $test = $this->getTest();

        $core  = $test->getMock(Core::class,[], [], '', false);
        $model = $test->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($test->once())
            ->method('setCore')
            ->with($core);

        $runtime = $this->createRuntime();
        $test->setProtectedProperty($runtime, 'model', $model);

        $runtime->setCore($core);
    }

    /**
     *
     */
    public function testApiManager_CallsModelMethod()
    {
        $test = $this->getTest();

        $manager = $test->getMock(RuntimeManager::class,[], [], '', false);
        $model = $test->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($test->once())
            ->method('getRuntimeManager')
            ->will($test->returnValue($manager));

        $runtime = $this->createRuntime();
        $test->setProtectedProperty($runtime, 'model', $model);

        $test->assertSame($manager, $runtime->getManager());
    }

    /**
     *
     */
    public function testApiGetLoop_CallsModelMethod()
    {
        $test = $this->getTest();

        $loop  = $test->getMock(Loop::class,[], [], '', false);
        $model = $test->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($test->once())
            ->method('getLoop')
            ->will($test->returnValue($loop));

        $runtime = $this->createRuntime();
        $test->setProtectedProperty($runtime, 'model', $model);

        $test->assertSame($loop, $runtime->getLoop());
    }

    /**
     *
     */
    public function testApiGetState_CallsModelMethod()
    {
        $test = $this->getTest();

        $state = 'state';
        $model = $test->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($test->once())
            ->method('getState')
            ->will($test->returnValue($state));

        $runtime = $this->createRuntime();
        $test->setProtectedProperty($runtime, 'model', $model);

        $test->assertSame($state, $runtime->getState());
    }

    /**
     * @dataProvider eventsProvider
     */
    public function testCaseAllOnMethods_RegisterHandlersForEvents($event)
    {
        $test = $this->getTest();

        $arg1 = 'arg1';
        $arg2 = 'arg2';

        $callable = $test->createCallableMock();
        $callable
            ->expects($test->once())
            ->method('__invoke')
            ->with($arg1, $arg2);

        $runtime = $this->createRuntime();
        $result  = call_user_func_array([ $runtime, 'on' . ucfirst($event) ], [ $callable ]);

        $test->assertInstanceOf(EventListener::class, $result);

        $runtime->emit($event, [ $arg1, $arg2 ]);
    }

    /**
     * @dataProvider statesProvider
     */
    public function testCaseAllIsStateMethods_CallsModelMethod($state)
    {
        $test = $this->getTest();

        $bool = true;
        $method = 'is' . ucfirst($state);

        $model = $test->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($test->once())
            ->method($method)
            ->will($test->returnValue($bool));

        $runtime = $this->createRuntime();
        $test->setProtectedProperty($runtime, 'model', $model);

        $result = call_user_func([ $runtime, $method ]);

        $test->assertSame($bool, $result);
    }

    /**
     * @dataProvider stateSwitchersProvider
     */
    public function testCaseAllSetStateMethods_CallsModelMethod($switcher)
    {
        $test = $this->getTest();

        $promise = $test->getMock(Promise::class, [], [], '', false);
        $method  = $switcher;

        $model = $test->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($test->once())
            ->method($method)
            ->will($test->returnValue($promise));

        $runtime = $this->createRuntime();
        $test->setProtectedProperty($runtime, 'model', $model);

        $result = call_user_func([ $runtime, $method ]);

        $test->assertSame($promise, $result);
    }

    /**
     *
     */
    public function testApiFail_CallsModelMethod()
    {
        $test = $this->getTest();

        $ex = new Exception;
        $params = [ 'param' => 'value' ];

        $model = $test->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($test->once())
            ->method('fail')
            ->with($ex, $params);

        $runtime = $this->createRuntime();
        $test->setProtectedProperty($runtime, 'model', $model);

        $runtime->fail($ex, $params);
    }

    /**
     *
     */
    public function testApiSucceed_CallsModelMethod()
    {
        $test = $this->getTest();

        $model = $test->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($test->once())
            ->method('succeed');

        $runtime = $this->createRuntime();
        $test->setProtectedProperty($runtime, 'model', $model);

        $runtime->succeed();
    }

    /**
     *
     */
    public function testProtectedApiInternalConfig_CallsConfigMethod()
    {
        $test = $this->getTest();

        $core = $test->getMock(Core::class, [], [], '', false);
        $result = 'result';

        $runtime = $this->createRuntime([], [ 'config' ]);
        $runtime
            ->expects($test->once())
            ->method('config')
            ->with($core)
            ->will($test->returnValue($result));

        $test->assertSame($result, $runtime->internalConfig($core));
    }

    /**
     *
     */
    public function testProtectedApiInternalBoot_CallsBootMethod()
    {
        $test = $this->getTest();

        $core = $test->getMock(Core::class, [], [], '', false);
        $result = 'result';

        $runtime = $this->createRuntime([], [ 'boot' ]);
        $runtime
            ->expects($test->once())
            ->method('boot')
            ->with($core)
            ->will($test->returnValue($result));

        $test->assertSame($result, $runtime->internalBoot($core));
    }

    /**
     *
     */
    public function testProtectedApiInternalConstruct_CallsConstructMethod()
    {
        $test = $this->getTest();

        $core = $test->getMock(Core::class, [], [], '', false);
        $result = 'result';

        $runtime = $this->createRuntime([], [ 'construct' ]);
        $runtime
            ->expects($test->once())
            ->method('construct')
            ->with($core)
            ->will($test->returnValue($result));

        $test->assertSame($result, $runtime->internalConstruct($core));
    }

    /**
     *
     */
    public function testProtectedApiConfig_ReturnsArray()
    {
        $test = $this->getTest();
        $core = $test->getMock(Core::class, [], [], '', false);

        $runtime = $this->createRuntime();
        $result  = $test->callProtectedMethod($runtime, 'config', [ $core ]);

        $test->assertSame([], $result);
    }

    /**
     *
     */
    public function testProtectedApiBoot_ReturnsSelf()
    {
        $test = $this->getTest();
        $core = $test->getMock(Core::class, [], [], '', false);

        $runtime = $this->createRuntime();
        $result  = $test->callProtectedMethod($runtime, 'boot', [ $core ]);

        $test->assertSame($runtime, $result);
    }

    /**
     *
     */
    public function testProtectedApiConstruct_ReturnsSelf()
    {
        $test = $this->getTest();
        $core = $test->getMock(Core::class, [], [], '', false);

        $runtime = $this->createRuntime();
        $result  = $test->callProtectedMethod($runtime, 'construct', [ $core ]);

        $test->assertSame($runtime, $result);
    }

    /**
     * @return string[][]
     */
    public function eventsProvider()
    {
        return [
            [ 'beforeCreate' ],
            [ 'create' ],
            [ 'afterCreate' ],
            [ 'beforeDestroy' ],
            [ 'destroy' ],
            [ 'afterDestroy' ],
            [ 'beforeStart' ],
            [ 'start' ],
            [ 'afterStart' ],
            [ 'beforeStop' ],
            [ 'stop' ],
            [ 'afterStop' ]
        ];
    }

    /**
     * @return string[][]
     */
    public function statesProvider()
    {
        return [
            [ 'created' ],
            [ 'destroyed' ],
            [ 'started' ],
            [ 'stopped' ],
            [ 'failed' ]
        ];
    }

    /**
     * @return string[][]
     */
    public function stateSwitchersProvider()
    {
        return [
            [ 'create' ],
            [ 'destroy' ],
            [ 'start' ],
            [ 'stop' ]
        ];
    }

    /**
     * @param string[] $params
     * @param string[]|null $methods
     * @return RuntimeContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract public function createRuntime($params = [], $methods = null);
}
