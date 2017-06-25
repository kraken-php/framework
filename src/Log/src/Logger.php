<?php

namespace Kraken\Log;

use Kraken\Log\Handler\HandlerInterface;
use Dazzle\Throwable\Exception\Logic\InstantiationException;
use Dazzle\Throwable\Exception\Runtime\WriteException;
use Dazzle\Util\Enum\EnumTrait;
use Monolog\Logger as Monolog;
use Error;
use Exception;

class Logger implements LoggerInterface
{
    use EnumTrait;

    /**
     * @var int
     */
    const DEBUG = Monolog::DEBUG;

    /**
     * @var int
     */
    const INFO = Monolog::INFO;

    /**
     * @var int
     */
    const NOTICE = Monolog::NOTICE;

    /**
     * @var int
     */
    const WARNING = Monolog::WARNING;

    /**
     * @var int
     */
    const ERROR = Monolog::ERROR;

    /**
     * @var int
     */
    const CRITICAL = Monolog::CRITICAL;

    /**
     * @var int
     */
    const ALERT = Monolog::ALERT;

    /**
     * @var int
     */
    const EMERGENCY = Monolog::EMERGENCY;

    /**
     * @var LoggerWrapper
     */
    protected $logger;

    /**
     * @param string $name
     * @param HandlerInterface[] $loggers
     * @param callable[] $processors
     * @throws InstantiationException
     */
    public function __construct($name, $loggers = [], $processors = [])
    {
        try
        {
            $this->logger = $this->createWrapper($name, $loggers, $processors);
        }
        catch (Error $ex)
        {
            throw new InstantiationException("Logger could not be constructed.", 0, $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException("Logger could not be constructed.", 0, $ex);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->logger);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getName()
    {
        return $this->logger->getName();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function pushHandler(HandlerInterface $handler)
    {
        $this->logger->pushHandler($handler);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function popHandler()
    {
        try
        {
            return $this->logger->popHandler();
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        return null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getHandlers()
    {
        return $this->logger->getHandlers();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function pushProcessor(callable $callback)
    {
        try
        {
            $this->logger->pushProcessor($callback);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Processor could not be pushed.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function popProcessor()
    {
        try
        {
            return $this->logger->popProcessor();
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        return null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getProcessors()
    {
        return $this->logger->getProcessors();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLevels()
    {
        $logger = $this->logger;
        return $logger::getLevels();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLevelName($level)
    {
        try
        {
            $logger = $this->logger;
            return $logger::getLevelName($level);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        return null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isHandling($level)
    {
        return $this->logger->isHandling($level);
    }

    /**
     * Add a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param int|string $level
     * @param string $message
     * @param string[] $context
     * @return bool
     * @throws WriteException
     */
    public function log($level, $message, array $context = array())
    {
        try
        {
            return $this->logger->log($level, $message, $context);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Record with undefined level could not be logged.", 0, $ex);
    }

    /**
     * Add a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message
     * @param string[] $context
     * @return bool
     * @throws WriteException
     */
    public function emergency($message, array $context = array())
    {
        try
        {
            return $this->logger->emergency($message, $context);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Record with debug level could not be logged.", 0, $ex);
    }

    /**
     * Add a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message
     * @param string[] $context
     * @return bool
     * @throws WriteException
     */
    public function alert($message, array $context = array())
    {
        try
        {
            return $this->logger->alert($message, $context);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Record with debug level could not be logged.", 0, $ex);
    }

    /**
     * Add a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message
     * @param string[] $context
     * @return bool
     * @throws WriteException
     */
    public function critical($message, array $context = array())
    {
        try
        {
            return $this->logger->critical($message, $context);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Record with debug level could not be logged.", 0, $ex);
    }

    /**
     * Add a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message
     * @param string[] $context
     * @return bool
     * @throws WriteException
     */
    public function error($message, array $context = array())
    {
        try
        {
            return $this->logger->error($message, $context);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Record with debug level could not be logged.", 0, $ex);
    }

    /**
     * Add a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message
     * @param string[] $context
     * @return bool
     * @throws WriteException
     */
    public function warning($message, array $context = array())
    {
        try
        {
            return $this->logger->warning($message, $context);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Record with warning level could not be logged.", 0, $ex);
    }

    /**
     * Add a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message
     * @param string[] $context
     * @return bool
     * @throws WriteException
     */
    public function info($message, array $context = array())
    {
        try
        {
            return $this->logger->info($message, $context);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Record with info level could not be logged.", 0, $ex);
    }

    /**
     * Add a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message
     * @param string[] $context
     * @return bool
     * @throws WriteException
     */
    public function notice($message, array $context = array())
    {
        try
        {
            return $this->logger->notice($message, $context);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Record with notice level could not be logged.", 0, $ex);
    }

    /**
     * Add a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message
     * @param string[] $context
     * @return bool
     * @throws WriteException
     */
    public function debug($message, array $context = array())
    {
        try
        {
            return $this->logger->debug($message, $context);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Record with debug level could not be logged.", 0, $ex);
    }

    /**
     * @param string $name
     * @param HandlerInterface[] $loggers
     * @param callable[] $processors
     * @return LoggerWrapper
     */
    protected function createWrapper($name, $loggers, $processors)
    {
        return new LoggerWrapper($name, $loggers, $processors);
    }
}
