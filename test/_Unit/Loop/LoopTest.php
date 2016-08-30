<?php

namespace Kraken\_Unit\Loop;

use Kraken\_Unit\Loop\_Mock\LoopModelMock;
use Kraken\Loop\Flow\FlowController;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopExtendedInterface;
use Kraken\Loop\LoopModelInterface;
use Kraken\Loop\Timer\Timer;
use Kraken\Test\TUnit;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

class LoopTest extends TUnit
{
    /**
     * @var ObjectProphecy
     */
    private $prophecy;

    /**
     * @var LoopModelMock|LoopModelInterface
     */
    private $model;

    /**
     * @var LoopExtendedInterface
     */
    private $loop;

    /**
     * @var resource
     */
    private $fp;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->prophecy = $this->prophesize(LoopModelMock::class);
        $this->model = $this->prophecy->reveal();
        $this->loop = $this->createLoop($this->model);
        $this->fp = fopen('php://temp', 'r+');
    }

    /**
     *
     */
    public function tearDown()
    {
        fclose($this->fp);

        parent::tearDown();
    }

    /**
     * @param object|LoopModelInterface $internal
     * @return LoopExtendedInterface
     */
    public function createLoop($internal)
    {
        return new Loop($internal);
    }

    /**
     * @return resource
     */
    public function createStreamMock()
    {
        return $this->fp;
    }

    /**
     * @return Timer
     */
    public function createTimerMock()
    {
        return new Timer($this->model, 0, function() {}, false, []);
    }

    /**
     * @param string $method
     * @param mixed[]|null $args
     * @param int $times
     * @return MethodProphecy
     */
    public function expect($method, $args = null, $times = 1)
    {
        $args = $args === null ? [ Argument::cetera() ] : $args;
        $mock = call_user_func_array([ $this->prophecy, $method ], $args);
        return $mock->shouldBeCalledTimes($times);
    }

    /**
     * @param string $method
     * @param mixed[]|null $args
     * @return MethodProphecy
     */
    public function prevent($method, $args = [])
    {
        return $this->expect($method, $args, 0);
    }

    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $loop = new Loop(new LoopModelMock);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $loop = new Loop(new LoopModelMock);
        unset($loop);
    }

    /**
     *
     */
    public function testApiGetModel_ReturnsModel()
    {
        $this->assertSame($this->model, $this->loop->getModel());
    }

    /**
     *
     */
    public function testApiErase_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $all = true;

        $this->expect('erase', [ $all ]);
        $loop->erase($all);
    }

    /**
     *
     */
    public function testApiErase_CallsMethodOnModel_WithDefaultParams()
    {
        $loop = $this->loop;

        $this->expect('erase', [ false ]);
        $loop->erase();
    }

    /**
     *
     */
    public function testApiErase_ReturnsCaller()
    {
        $loop = $this->loop;

        $this->expect('erase');
        $this->assertSame($loop, $loop->erase());
    }

    /**
     *
     */
    public function testApiExport_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $target = new Loop($model = new LoopModelMock);
        $all = true;

        $this->expect('export', [ $model, $all ]);
        $loop->export($target, $all);
    }

    /**
     *
     */
    public function testApiExport_CallsMethodOnModel_WithDefaultParams()
    {
        $loop = $this->loop;
        $target = new Loop($model = new LoopModelMock);

        $this->expect('export', [ $model, false ]);
        $loop->export($target);
    }

    /**
     *
     */
    public function testApiExport_ReturnsCaller()
    {
        $loop = $this->loop;
        $target = new Loop($model = new LoopModelMock);

        $this->expect('export');
        $this->assertSame($loop, $loop->export($target));
    }

    /**
     *
     */
    public function testApiImport_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $target = new Loop($model = new LoopModelMock);
        $all = true;

        $this->expect('import', [ $model, $all ]);
        $loop->import($target, $all);
    }

    /**
     *
     */
    public function testApiImport_CallsMethodOnModel_WithDefaultParams()
    {
        $loop = $this->loop;
        $target = new Loop($model = new LoopModelMock);

        $this->expect('import', [ $model, false ]);
        $loop->import($target);
    }

    /**
     *
     */
    public function testApiImport_ReturnsCaller()
    {
        $loop = $this->loop;
        $target = new Loop($model = new LoopModelMock);

        $this->expect('import');
        $this->assertSame($loop, $loop->import($target));
    }

    /**
     *
     */
    public function testApiSwap_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $target = new Loop($model = new LoopModelMock);
        $all = true;

        $this->expect('swap', [ $model, $all ]);
        $loop->swap($target, $all);
    }

    /**
     *
     */
    public function testApiSwap_CallsMethodOnModel_WithDefaultParams()
    {
        $loop = $this->loop;
        $target = new Loop($model = new LoopModelMock);

        $this->expect('swap', [ $model, false ]);
        $loop->swap($target);
    }

    /**
     *
     */
    public function testApiSwap_ReturnsCaller()
    {
        $loop = $this->loop;
        $target = new Loop($model = new LoopModelMock);

        $this->expect('swap');
        $this->assertSame($loop, $loop->swap($target));
    }

    /**
     *
     */
    public function testApiIsRunning_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $result = true;

        $this->expect('isRunning', [])->willReturn($result);
        $this->assertSame($result, $loop->isRunning());
    }

    /**
     *
     */
    public function testApiAddReadStream_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $stream = $this->createStreamMock();
        $listener = function() {};

        $this->expect('addReadStream', [ $stream, $listener ]);
        $loop->addReadStream($stream, $listener);
    }

    /**
     *
     */
    public function testApiAddWriteStream_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $stream = $this->createStreamMock();
        $listener = function() {};

        $this->expect('addWriteStream', [ $stream, $listener ]);
        $loop->addWriteStream($stream, $listener);
    }

    /**
     *
     */
    public function testApiRemoveReadStream_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $stream = $this->createStreamMock();

        $this->expect('removeReadStream', [ $stream ]);
        $loop->removeReadStream($stream);
    }

    /**
     *
     */
    public function testApiRemoveWriteStream_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $stream = $this->createStreamMock();

        $this->expect('removeWriteStream', [ $stream ]);
        $loop->removeWriteStream($stream);
    }

    /**
     *
     */
    public function testApiRemoveStream_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $stream = $this->createStreamMock();

        $this->expect('removeStream', [ $stream ]);
        $loop->removeStream($stream);
    }

    /**
     *
     */
    public function testApiAddTimer_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $interval = 1;
        $callback = function() {};

        $this->expect('addTimer', [ $interval, $callback ]);
        $loop->addTimer($interval, $callback);
    }

    /**
     *
     */
    public function testApiAddPeriodicTimer_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $interval = 1;
        $callback = function() {};

        $this->expect('addPeriodicTimer', [ $interval, $callback ]);
        $loop->addPeriodicTimer($interval, $callback);
    }

    /**
     *
     */
    public function testApiCancelTimer_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $timer = $this->createTimerMock();

        $this->expect('cancelTimer', [ $timer ]);
        $loop->cancelTimer($timer);
    }

    /**
     *
     */
    public function testApiIsTimerActive_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $timer = $this->createTimerMock();

        $this->expect('isTimerActive', [ $timer ]);
        $loop->isTimerActive($timer);
    }

    /**
     *
     */
    public function testApiOnStart_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $listener = function() {};

        $this->expect('onStart', [ $listener ]);
        $loop->onStart($listener);
    }

    /**
     *
     */
    public function testApiOnStop_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $listener = function() {};

        $this->expect('onStop', [ $listener ]);
        $loop->onStop($listener);
    }

    /**
     *
     */
    public function testApiOnTick_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $listener = function() {};

        $this->expect('onAfterTick', [ $listener ]);
        $loop->onTick($listener);
    }

    /**
     *
     */
    public function testApiOnBeforeTick_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $listener = function() {};

        $this->expect('onBeforeTick', [ $listener ]);
        $loop->onBeforeTick($listener);
    }

    /**
     *
     */
    public function testApiOnAfterTick_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $listener = function() {};

        $this->expect('onAfterTick', [ $listener ]);
        $loop->onAfterTick($listener);
    }

    /**
     *
     */
    public function testApiTick_CallsMethodOnModel()
    {
        $loop = $this->loop;

        $this->expect('tick', []);
        $loop->tick();
    }

    /**
     *
     */
    public function testApiStart_CallsMethodOnModel()
    {
        $loop = $this->loop;

        $this->expect('start', []);
        $loop->start();
    }

    /**
     *
     */
    public function testApiStop_CallsMethodOnModel()
    {
        $loop = $this->loop;

        $this->expect('stop', []);
        $loop->stop();
    }

    /**
     *
     */
    public function testApiSetFlowController_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $controller = new FlowController;

        $this->expect('setFlowController', [ $controller ]);
        $loop->setFlowController($controller);
    }

    /**
     *
     */
    public function testApiGetFlowController_CallsMethodOnModel()
    {
        $loop = $this->loop;
        $controller = new FlowController;

        $this->expect('getFlowController', [])->willReturn($controller);
        $this->assertSame($controller, $loop->getFlowController());
    }
}
