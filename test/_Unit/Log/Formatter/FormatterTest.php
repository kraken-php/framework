<?php

namespace Kraken\_Unit\Log\Formatter;

use Kraken\Log\Formatter\Formatter;
use Kraken\Test\TUnit;
use Monolog\Formatter\FormatterInterface as MonologFormatterInterface;
use Monolog\Formatter\LineFormatter;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

class FormatterTest extends TUnit
{
    /**
     * @var ObjectProphecy
     */
    private $prophecy;

    /**
     * @var MonologFormatterInterface
     */
    private $model;

    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createFormatter();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $formatter = $this->createFormatter();
        unset($formatter);
    }

    /**
     *
     */
    public function testApiGetModel_ReturnsModel()
    {
        $formatter = $this->createFormatter();

        $this->assertSame($this->model, $formatter->getModel());
    }

    /**
     *
     */
    public function testApiFormat_CallsMethodOnModel()
    {
        $formatter = $this->createFormatter();
        $array = [ 'some' ];
        $val   = 'val';

        $this->expect('format', [ $array ])->willReturn($val);
        $this->assertSame($val, $formatter->format($array));
    }

    /**
     *
     */
    public function testApiFormatBatch_CallsMethodOnModel()
    {
        $formatter = $this->createFormatter();
        $array = [ 'some' ];
        $val   = 'val';

        $this->expect('formatBatch', [ $array ])->willReturn($val);
        $this->assertSame($val, $formatter->formatBatch($array));
    }

    /**
     * @return Formatter
     */
    public function createFormatter()
    {
        $this->prophecy = $this->prophesize(LineFormatter::class);
        $this->model = $this->prophecy->reveal();

        return new Formatter($this->model);
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
