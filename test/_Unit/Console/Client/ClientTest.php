<?php

namespace Kraken\_Unit\Console\Client;

use Kraken\Console\Client\ConsoleClient;
use Kraken\Core\Core;
use Kraken\Event\EventHandler;
use Kraken\Loop\Loop;
use Kraken\Runtime\RuntimeContainer;
use Kraken\Test\TUnit;

class ClientTest extends TUnit
{
    /**
     *
     */
    public function testApiType_ReturnsType()
    {
        $test = $this->getTest();

        $result = 'RuntimeType';
        $mock = $test->getMock(Core::class, [ 'unit' ], [], '', false);
        $mock
            ->expects($test->once())
            ->method('unit')
            ->will($test->returnValue($result));

        $runtime = $this->createRuntime([ 'getCore' ]);
        $runtime
            ->expects($test->once())
            ->method('getCore')
            ->will($test->returnValue($mock));

        $test->assertSame($result, $runtime->type());
    }

    /**
     *
     */
    public function testApiParent_ReturnsNull()
    {
        $test = $this->getTest();
        $runtime = $this->createRuntime();

        $test->assertSame(null, $runtime->parent());
    }

    /**
     *
     */
    public function testApiAlias_ReturnsAlias()
    {
        $test = $this->getTest();
        $runtime = $this->createRuntime();

        $test->assertSame('ConsoleClient', $runtime->alias());
    }

    /**
     *
     */
    public function testApiName_ReturnsName()
    {
        $test = $this->getTest();
        $runtime = $this->createRuntime();

        $test->assertSame('ConsoleClient', $runtime->name());
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

        $test->assertInstanceOf(EventHandler::class, $result);

        $runtime->emit($event, [ $arg1, $arg2 ]);
    }

    /**
     *
     */
    public function testApiStart_EmitsEvents()
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('start');
        $loop
            ->expects($this->once())
            ->method('afterTick')
            ->will($this->returnCallback(function($callable) {
                $callable();
            }));

        $runtime = $this->createRuntime([ 'getLoop' ]);
        $runtime
            ->expects($this->atLeastOnce())
            ->method('getLoop')
            ->will($this->returnValue($loop));

        $runtime->on('start', $this->expectCallableOnce());
        $runtime->on('command', $this->expectCallableOnce());

        $runtime->start();
    }

    /**
     *
     */
    public function testApiStop_EmitsEvents()
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->once())
            ->method('stop');

        $runtime = $this->createRuntime([ 'getLoop' ]);
        $runtime
            ->expects($this->atLeastOnce())
            ->method('getLoop')
            ->will($this->returnValue($loop));

        $runtime->on('stop', $this->expectCallableOnce());

        $runtime->stop();
    }

    /**
     *
     */
    public function testProtectedApiInternalConfig_CallsConfigMethod()
    {
        $test = $this->getTest();
        $core = $test->getMock(Core::class, [], [], '', false);
        $result = 'result';

        $runtime = $this->createRuntime([ 'config' ]);
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

        $runtime = $this->createRuntime([ 'boot' ]);
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

        $runtime = $this->createRuntime([ 'construct' ]);
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
            [ 'start' ],
            [ 'stop' ],
            [ 'command' ]
        ];
    }

    /**
     * @param string[]|null $methods
     * @return RuntimeContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRuntime($methods = null)
    {

        return $this->getMock(ConsoleClient::class, $methods, []);
    }
}
