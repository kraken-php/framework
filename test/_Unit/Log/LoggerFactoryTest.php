<?php

namespace Kraken\_Unit\Log;

use Kraken\Log\Formatter\FormatterInterface;
use Kraken\Log\Handler\HandlerInterface;
use Kraken\Log\Logger;
use Kraken\Log\LoggerFactory;
use Kraken\Test\TUnit;
use Dazzle\Throwable\Exception\Logic\InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Processor\TagProcessor;

class LoggerFactoryTest extends TUnit
{
    /**
     *
     */
    public function testApiCreateHandler_ReturnsHandlerWithRightArguments_ForValidMonologHandlerSpecifiedByName()
    {
        $factory = $this->createLoggerFactory();

        $handler = $factory->createHandler('NullHandler', [ Logger::EMERGENCY ]);

        $this->assertInstanceOf(HandlerInterface::class, $handler);
        $this->assertInstanceOf(NullHandler::class, $handler->getModel());
        $this->assertTrue($handler->isHandling([ 'level' => Logger::EMERGENCY ]));
    }

    /**
     *
     */
    public function testApiCreateHandler_ReturnsHandlerWithRightArguments_ForValidMonologHandlerSpecifiedByClass()
    {
        $factory = $this->createLoggerFactory();

        $handler = $factory->createHandler(NullHandler::class, [ Logger::EMERGENCY ]);

        $this->assertInstanceOf(HandlerInterface::class, $handler);
        $this->assertInstanceOf(NullHandler::class, $handler->getModel());
        $this->assertTrue($handler->isHandling([ 'level' => Logger::EMERGENCY ]));
    }

    /**
     *
     */
    public function testApiCreateHandler_ThrowsException_WhenNonExistingMonologHandlerSpecified()
    {
        $factory = $this->createLoggerFactory();

        $this->setExpectedException(InvalidArgumentException::class);
        $factory->createHandler('NonExisting', [ Logger::EMERGENCY ]);
    }

    /**
     *
     */
    public function testApiCreateFormatter_ReturnsFormatterWithRightArguments_ForValidMonologFormatterSpecifiedByName()
    {
        $factory = $this->createLoggerFactory();

        $handler = $factory->createFormatter('LineFormatter', []);

        $this->assertInstanceOf(FormatterInterface::class, $handler);
        $this->assertInstanceOf(LineFormatter::class, $handler->getModel());
    }

    /**
     *
     */
    public function testApiCreateFormatter_ReturnsFormatterWithRightArguments_ForValidMonologFormatterSpecifiedByClass()
    {
        $factory = $this->createLoggerFactory();

        $handler = $factory->createFormatter(LineFormatter::class, []);

        $this->assertInstanceOf(FormatterInterface::class, $handler);
        $this->assertInstanceOf(LineFormatter::class, $handler->getModel());
    }

    /**
     *
     */
    public function testApiCreateFormatter_ThrowsException_WhenNonExistingMonologFormatterSpecified()
    {
        $factory = $this->createLoggerFactory();

        $this->setExpectedException(InvalidArgumentException::class);
        $factory->createFormatter('NonExisting', []);
    }

    /**
     *
     */
    public function testApiCreateProcessor_ReturnsProcessorWithRightArguments_ForValidMonologProcessorSpecifiedByName()
    {
        $factory = $this->createLoggerFactory();
        $tags = [ 'tag' => 'myTag' ];

        $handler = $factory->createProcessor('TagProcessor', [ $tags ]);

        $this->assertInstanceOf(TagProcessor::class, $handler);
        $this->assertSame([ 'extra' => [ 'tags' => $tags ] ], $handler([ 'extra' => [] ]));
    }

    /**
     *
     */
    public function testApiCreateProcessor_ReturnsProcessorWithRightArguments_ForValidMonologProcessorSpecifiedByClass()
    {
        $factory = $this->createLoggerFactory();
        $tags = [ 'tag' => 'myTag' ];

        $handler = $factory->createProcessor(TagProcessor::class, [ $tags ]);

        $this->assertInstanceOf(TagProcessor::class, $handler);
        $this->assertSame([ 'extra' => [ 'tags' => $tags ] ], $handler([ 'extra' => [] ]));
    }

    /**
     *
     */
    public function testApiCreateProcessor_ThrowsException_WhenNonExistingMonologProcessorSpecified()
    {
        $factory = $this->createLoggerFactory();
        $tags = [ 'tag' => 'myTag' ];

        $this->setExpectedException(InvalidArgumentException::class);
        $factory->createProcessor('NonExisting', [ $tags ]);
    }

    /**
     * @return LoggerFactory
     */
    public function createLoggerFactory()
    {
        return new LoggerFactory();
    }
}
