<?php

namespace Kraken\_Unit\Log;

use Exception;
use Kraken\_Unit\Log\_Mock\LoggerMock;
use Kraken\Log\Handler\Handler;
use Kraken\Log\Handler\HandlerInterface;
use Kraken\Log\Logger;
use Kraken\Log\LoggerWrapper;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Runtime\WriteException;
use Monolog\Handler\NullHandler;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

class LoggerTest extends TUnit
{
    /**
     * @var string
     */
    private $name = 'app';

    /**
     * @var ObjectProphecy
     */
    private $prophecy;

    /**
     * @var LoggerWrapper
     */
    private $model;

    /**
     *
     */
    public function testApiConstructor_DoesNotThrowExceptions()
    {
        $this->createLogger();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowExceptions()
    {
        $logger = $this->createLogger();
        unset($logger);
    }

    /**
     *
     */
    public function testApiGetName_ReturnsLoggerName()
    {
        $logger = $this->createLogger();

        $this->assertSame($this->name, $logger->getName());
    }

    /**
     *
     */
    public function testApiPushHandler_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $handler = new Handler(new NullHandler());

        $this->expect('pushHandler', [ $handler ]);
        $logger->pushHandler($handler);
    }

    /**
     *
     */
    public function testApiPopHandler_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $handler = new Handler(new NullHandler());

        $this->expect('popHandler', [])->willReturn($handler);
        $this->assertSame($handler, $logger->popHandler());
    }

    /**
     *
     */
    public function testApiPopHandler_ReturnsNull_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $ex = new Exception();

        $this->expect('popHandler', [])->willThrow($ex);
        $this->assertSame(null, $logger->popHandler());
    }

    /**
     *
     */
    public function testApiGetHandlers_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $handler = new Handler(new NullHandler());
        $handlers = [ $handler ];

        $this->expect('getHandlers', [])->willReturn($handlers);
        $this->assertSame($handlers, $logger->getHandlers());
    }

    /**
     *
     */
    public function testApiPushProcessor_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $callable = function() {};

        $this->expect('pushProcessor', [ $callable ]);
        $logger->pushProcessor($callable);
    }

    /**
     *
     */
    public function testApiPushProcessor_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $callable = function() {};
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('pushProcessor', [ $callable ])->willThrow($ex);
        $logger->pushProcessor($callable);
    }

    /**
     *
     */
    public function testApiPopProcessor_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $callable = function() {};

        $this->expect('popProcessor', [])->willReturn($callable);
        $this->assertSame($callable, $logger->popProcessor());
    }

    /**
     *
     */
    public function testApiPopProcessor_ReturnsNull_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $ex = new Exception();

        $this->expect('popProcessor', [])->willThrow($ex);
        $this->assertSame(null, $logger->popProcessor());
    }

    /**
     *
     */
    public function testApiIsHandling_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $level = 'some';
        $result = 'result';

        $this->expect('isHandling', [ $level ])->willReturn($result);
        $this->assertSame($result, $logger->isHandling($level));
    }

    /**
     *
     */
    public function testApiLog_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $level = 'level';
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $result = true;

        $this->expect('log', [ $level, $message, $context ])->willReturn($result);
        $this->assertSame($result, $logger->log($level, $message, $context));
    }

    /**
     *
     */
    public function testApiLog_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $level = 'level';
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('log', [ $level, $message, $context ])->willThrow($ex);
        $logger->log($level, $message, $context);
    }

    /**
     *
     */
    public function testApiEmergency_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $result = true;

        $this->expect('emergency', [ $message, $context ])->willReturn($result);
        $this->assertSame($result, $logger->emergency($message, $context));
    }

    /**
     *
     */
    public function testApiEmergency_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('emergency', [ $message, $context ])->willThrow($ex);
        $logger->emergency($message, $context);
    }

    /**
     *
     */
    public function testApiAlert_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $result = true;

        $this->expect('alert', [ $message, $context ])->willReturn($result);
        $this->assertSame($result, $logger->alert($message, $context));
    }

    /**
     *
     */
    public function testApiAlert_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('alert', [ $message, $context ])->willThrow($ex);
        $logger->alert($message, $context);
    }

    /**
     *
     */
    public function testApiCritical_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $result = true;

        $this->expect('critical', [ $message, $context ])->willReturn($result);
        $this->assertSame($result, $logger->critical($message, $context));
    }

    /**
     *
     */
    public function testApiCritical_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('critical', [ $message, $context ])->willThrow($ex);
        $logger->critical($message, $context);
    }

    /**
     *
     */
    public function testApiError_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $result = true;

        $this->expect('error', [ $message, $context ])->willReturn($result);
        $this->assertSame($result, $logger->error($message, $context));
    }

    /**
     *
     */
    public function testApiError_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('error', [ $message, $context ])->willThrow($ex);
        $logger->error($message, $context);
    }

    /**
     *
     */
    public function testApiWarning_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $result = true;

        $this->expect('warning', [ $message, $context ])->willReturn($result);
        $this->assertSame($result, $logger->warning($message, $context));
    }

    /**
     *
     */
    public function testApiWarning_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('warning', [ $message, $context ])->willThrow($ex);
        $logger->warning($message, $context);
    }

    /**
     *
     */
    public function testApiInfo_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $result = true;

        $this->expect('info', [ $message, $context ])->willReturn($result);
        $this->assertSame($result, $logger->info($message, $context));
    }

    /**
     *
     */
    public function testApiInfo_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('info', [ $message, $context ])->willThrow($ex);
        $logger->info($message, $context);
    }

    /**
     *
     */
    public function testApiNotice_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $result = true;

        $this->expect('notice', [ $message, $context ])->willReturn($result);
        $this->assertSame($result, $logger->notice($message, $context));
    }

    /**
     *
     */
    public function testApiNotice_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('notice', [ $message, $context ])->willThrow($ex);
        $logger->notice($message, $context);
    }

    /**
     *
     */
    public function testApiDebug_CallsMethodOnModel()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $result = true;

        $this->expect('debug', [ $message, $context ])->willReturn($result);
        $this->assertSame($result, $logger->debug($message, $context));
    }

    /**
     *
     */
    public function testApiDebug_ThrowsException_WhenModelThrowsException()
    {
        $logger = $this->createLoggerMock();
        $message = 'message';
        $context = [ 'a' => 'A' ];
        $ex = new Exception();

        $this->setExpectedException(WriteException::class);
        $this->expect('debug', [ $message, $context ])->willThrow($ex);
        $logger->debug($message, $context);
    }

    /**
     *
     */
    public function testApiCreateWrapper_CreatesWrapper()
    {
        $logger = $this->createLoggerMock();

        $wrapper = $this->callProtectedMethod($logger, 'createWrapper', [ 'name', [], [] ]);

        $this->assertInstanceOf(LoggerWrapper::class, $wrapper);
    }

    /**
     * @param HandlerInterface[] $loggers
     * @param callable[] $processors
     * @return Logger
     */
    public function createLogger($loggers = [], $processors = [])
    {
        return new Logger($this->name, $loggers, $processors);
    }

    /**
     * @return Logger
     */
    public function createLoggerMock()
    {
        $logger = new LoggerMock('');

        $this->prophecy = $this->prophesize(LoggerWrapper::class);
        $this->model = $this->prophecy->reveal();

        $logger->setWrapper($this->model);

        return $logger;
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
