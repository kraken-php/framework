<?php

namespace Kraken\_Unit\Log\Handler;

use Kraken\Log\Formatter\Formatter;
use Kraken\Log\Handler\Handler;
use Kraken\Test\TUnit;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Monolog\Handler\NullHandler;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

class HandlerTest extends TUnit
{
    /**
     * @var ObjectProphecy
     */
    private $prophecy;

    /**
     * @var MonologHandlerInterface
     */
    private $model;

    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createHandler();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $handler = $this->createHandler();
        unset($handler);
    }

    /**
     *
     */
    public function testApiGetModel_ReturnsModel()
    {
        $handler = $this->createHandler();

        $this->assertSame($this->model, $handler->getModel());
    }

    /**
     *
     */
    public function testApiIsHandling_CallsMethodOnModel()
    {
        $handler = $this->createHandler();
        $array = [ 'some' ];
        $val   = 'val';

        $this->expect('isHandling', [ $array ])->willReturn($val);
        $this->assertSame($val, $handler->isHandling($array));
    }

    /**
     *
     */
    public function testApiHandle_CallsMethodOnModel()
    {
        $handler = $this->createHandler();
        $array = [ 'some' ];
        $val   = 'val';

        $this->expect('handle', [ $array ])->willReturn($val);
        $this->assertSame($val, $handler->handle($array));
    }

    /**
     *
     */
    public function testApiHandleBatch_CallsMethodOnModel()
    {
        $handler = $this->createHandler();
        $array = [ 'some' ];
        $val   = 'val';

        $this->expect('handleBatch', [ $array ])->willReturn($val);
        $this->assertSame($val, $handler->handleBatch($array));
    }

    /**
     *
     */
    public function testApiPushProcessor_CallsMethodOnModel()
    {
        $handler = $this->createHandler();
        $callable = function() {};
        $result = function() {};

        $this->expect('pushProcessor', [ $callable ])->willReturn($result);
        $this->assertSame($result, $handler->pushProcessor($callable));
    }

    /**
     *
     */
    public function testApiPopProcessor_CallsMethodOnModel()
    {
        $handler = $this->createHandler();
        $callable = function() {};

        $this->expect('popProcessor', [])->willReturn($callable);
        $this->assertSame($callable, $handler->popProcessor());
    }

    /**
     *
     */
    public function testApiSetFormatter_CallsMethodOnModel()
    {
        $handler = $this->createHandler();
        $formatter = new Formatter(new LineFormatter);
        $result = $formatter;

        $this->expect('setFormatter', [ $formatter ])->willReturn($result);
        $this->assertSame($result, $handler->setFormatter($formatter));
    }

    /**
     *
     */
    public function testApiGetFormatter_CallsMethodOnModel()
    {
        $handler = $this->createHandler();
        $formatter = new Formatter(new LineFormatter);

        $this->expect('getFormatter', [])->willReturn($formatter);
        $this->assertSame($formatter, $handler->getFormatter());
    }

    /**
     * @return Handler
     */
    public function createHandler()
    {
        $this->prophecy = $this->prophesize(NullHandler::class);
        $this->model = $this->prophecy->reveal();

        return new Handler($this->model);
    }

    /**
     * @param string $method
     * @param mixed[] $args
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
     * @param mixed[] $args
     * @return MethodProphecy
     */
    public function prevent($method, $args = null)
    {
        return $this->expect($method, $args, 0);
    }
}
