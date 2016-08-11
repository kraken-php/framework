<?php

namespace Kraken\_Unit\Runtime\_Case;

use Kraken\Core\Core;
use Kraken\Event\EventEmitter;
use Kraken\Event\EventHandler;
use Kraken\Loop\Loop;
use Kraken\Promise\Promise;
use Kraken\Runtime\RuntimeContainer;
use Kraken\Runtime\RuntimeInterface;
use Kraken\Runtime\RuntimeManager;
use Kraken\Runtime\RuntimeModel;
use Kraken\Test\TUnit;
use Exception;

abstract class RuntimeCase extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $runtime = $this->createRuntime();

        $this->assertInstanceOf(RuntimeContainer::class, $runtime);
        $this->assertInstanceOf(RuntimeInterface::class, $runtime);
        $this->assertInstanceOf(EventEmitter::class, $runtime);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $runtime = $this->createRuntime();
        unset($runtime);
    }

    /**
     *
     */
    public function testApiModel_ReturnsModel()
    {
        $runtime = $this->createRuntime();

        $this->assertInstanceOf(RuntimeModel::class, $runtime->model());
    }

    /**
     *
     */
    public function testApiType_ReturnsType()
    {
        $result = 'RuntimeType';

        $mock = $this->getMock(Core::class, [ 'unit' ], [], '', false);
        $mock
            ->expects($this->once())
            ->method('unit')
            ->will($this->returnValue($result));

        $runtime = $this->createRuntime([], [ 'getCore' ]);
        $runtime
            ->expects($this->once())
            ->method('getCore')
            ->will($this->returnValue($mock));

        $this->assertSame($result, $runtime->type());
    }

    /**
     *
     */
    public function testApiParent_ReturnsParent()
    {
        $runtime = $this->createRuntime([ $parent = 'someParent' ]);

        $this->assertSame($parent, $runtime->parent());
    }

    /**
     *
     */
    public function testApiAlias_ReturnsAlias()
    {
        $runtime = $this->createRuntime([ 'parent', $alias = 'someAlias' ]);

        $this->assertSame($alias, $runtime->alias());
    }

    /**
     *
     */
    public function testApiName_ReturnsName()
    {
        $runtime = $this->createRuntime([ 'parent', 'alias', $name = 'someName' ]);

        $this->assertSame($name, $runtime->name());
    }

    /**
     *
     */
    public function testApiGetCore_CallsModelMethod()
    {
        $core  = $this->getMock(Core::class,[], [], '', false);
        $model = $this->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($this->once())
            ->method('getCore')
            ->will($this->returnValue($core));

        $runtime = $this->createRuntime();
        $this->setProtectedProperty($runtime, 'model', $model);

        $this->assertSame($core, $runtime->getCore());
    }

    /**
     *
     */
    public function testApiSetCore_CallsModelMethod()
    {
        $core  = $this->getMock(Core::class,[], [], '', false);
        $model = $this->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($this->once())
            ->method('setCore')
            ->with($core);

        $runtime = $this->createRuntime();
        $this->setProtectedProperty($runtime, 'model', $model);

        $runtime->setCore($core);
    }

    /**
     *
     */
    public function testApiManager_CallsModelMethod()
    {
        $manager = $this->getMock(RuntimeManager::class,[], [], '', false);
        $model = $this->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($this->once())
            ->method('getRuntimeManager')
            ->will($this->returnValue($manager));

        $runtime = $this->createRuntime();
        $this->setProtectedProperty($runtime, 'model', $model);

        $this->assertSame($manager, $runtime->manager());
    }

    /**
     *
     */
    public function testApiGetLoop_CallsModelMethod()
    {
        $loop  = $this->getMock(Loop::class,[], [], '', false);
        $model = $this->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($this->once())
            ->method('getLoop')
            ->will($this->returnValue($loop));

        $runtime = $this->createRuntime();
        $this->setProtectedProperty($runtime, 'model', $model);

        $this->assertSame($loop, $runtime->getLoop());
    }

    /**
     *
     */
    public function testApiState_CallsModelMethod()
    {
        $state = 'state';
        $model = $this->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($this->once())
            ->method('getState')
            ->will($this->returnValue($state));

        $runtime = $this->createRuntime();
        $this->setProtectedProperty($runtime, 'model', $model);

        $this->assertSame($state, $runtime->state());
    }

    /**
     * @dataProvider eventsProvider
     */
    public function testCaseAllOnMethods_RegisterHandlersForEvents($event)
    {
        $arg1 = 'arg1';
        $arg2 = 'arg2';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($arg1, $arg2);

        $runtime = $this->createRuntime();
        $result  = call_user_func_array([ $runtime, 'on' . ucfirst($event) ], [ $callable ]);

        $this->assertInstanceOf(EventHandler::class, $result);

        $runtime->emit($event, [ $arg1, $arg2 ]);
    }

    /**
     * @dataProvider statesProvider
     */
    public function testCaseAllIsStateMethods_CallsModelMethod($state)
    {
        $bool = true;
        $method = 'is' . ucfirst($state);

        $model = $this->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($bool));

        $runtime = $this->createRuntime();
        $this->setProtectedProperty($runtime, 'model', $model);

        $result = call_user_func([ $runtime, $method ]);

        $this->assertSame($bool, $result);
    }

    /**
     * @dataProvider stateSwitchersProvider
     */
    public function testCaseAllSetStateMethods_CallsModelMethod($switcher)
    {
        $promise = $this->getMock(Promise::class, [], [], '', false);
        $method  = $switcher;

        $model = $this->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($this->once())
            ->method($method)
            ->will($this->returnValue($promise));

        $runtime = $this->createRuntime();
        $this->setProtectedProperty($runtime, 'model', $model);

        $result = call_user_func([ $runtime, $method ]);

        $this->assertSame($promise, $result);
    }

    /**
     *
     */
    public function testApiFail_CallsModelMethod()
    {
        $ex = new Exception;
        $params = [ 'param' => 'value' ];

        $model = $this->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($this->once())
            ->method('fail')
            ->with($ex, $params);

        $runtime = $this->createRuntime();
        $this->setProtectedProperty($runtime, 'model', $model);

        $runtime->fail($ex, $params);
    }

    /**
     *
     */
    public function testApiSucceed_CallsModelMethod()
    {
        $model = $this->getMock(RuntimeModel::class, [], [], '', false);
        $model
            ->expects($this->once())
            ->method('succeed');

        $runtime = $this->createRuntime();
        $this->setProtectedProperty($runtime, 'model', $model);

        $runtime->succeed();
    }

    /**
     *
     */
    public function testProtectedApiInternalConfig_CallsConfigMethod()
    {
        $core = $this->getMock(Core::class, [], [], '', false);
        $result = 'result';

        $runtime = $this->createRuntime([], [ 'config' ]);
        $runtime
            ->expects($this->once())
            ->method('config')
            ->with($core)
            ->will($this->returnValue($result));

        $this->assertSame($result, $runtime->internalConfig($core));
    }

    /**
     *
     */
    public function testProtectedApiInternalBoot_CallsBootMethod()
    {
        $core = $this->getMock(Core::class, [], [], '', false);
        $result = 'result';

        $runtime = $this->createRuntime([], [ 'boot' ]);
        $runtime
            ->expects($this->once())
            ->method('boot')
            ->with($core)
            ->will($this->returnValue($result));

        $this->assertSame($result, $runtime->internalBoot($core));
    }

    /**
     *
     */
    public function testProtectedApiInternalConstruct_CallsConstructMethod()
    {
        $core = $this->getMock(Core::class, [], [], '', false);
        $result = 'result';

        $runtime = $this->createRuntime([], [ 'construct' ]);
        $runtime
            ->expects($this->once())
            ->method('construct')
            ->with($core)
            ->will($this->returnValue($result));

        $this->assertSame($result, $runtime->internalConstruct($core));
    }

    /**
     *
     */
    public function testProtectedApiConfig_ReturnsArray()
    {
        $core = $this->getMock(Core::class, [], [], '', false);

        $runtime = $this->createRuntime();
        $result  = $this->callProtectedMethod($runtime, 'config', [ $core ]);

        $this->assertSame([], $result);
    }

    /**
     *
     */
    public function testProtectedApiBoot_ReturnsSelf()
    {
        $core = $this->getMock(Core::class, [], [], '', false);

        $runtime = $this->createRuntime();
        $result  = $this->callProtectedMethod($runtime, 'boot', [ $core ]);

        $this->assertSame($runtime, $result);
    }

    /**
     *
     */
    public function testProtectedApiConstruct_ReturnsSelf()
    {
        $core = $this->getMock(Core::class, [], [], '', false);

        $runtime = $this->createRuntime();
        $result  = $this->callProtectedMethod($runtime, 'construct', [ $core ]);

        $this->assertSame($runtime, $result);
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
            [ 'stopped' ]
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
