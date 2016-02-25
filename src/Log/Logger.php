<?php

namespace Kraken\Log;

use Kraken\Exception\Runtime\InstantiationException;
use Kraken\Exception\Io\WriteException;
use Kraken\Log\Handler\HandlerInterface;
use Monolog\Logger as Monolog;
use Error;
use Exception;

class Logger implements LoggerInterface
{
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
     * @var string
     */
    protected $loggerClass;

    /**
     * @param string $name
     * @param HandlerInterface[] $loggersList
     * @param callable[] $processorsList
     * @throws InstantiationException
     */
    public function __construct($name, $loggersList = [], $processorsList = [])
    {
        try
        {
            $this->logger = new LoggerWrapper($name, $loggersList, $processorsList);
            $this->loggerClass = get_class($this->logger);
        }
        catch (Error $ex)
        {
            throw new InstantiationException("Logger could not be constructed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException("Logger could not be constructed.", $ex);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->logger);
        unset($this->loggerClass);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->logger->getName();
    }

    /**
     * Pushes a handler on to the stack.
     *
     * @param HandlerInterface $handler
     */
    public function pushHandler(HandlerInterface $handler)
    {
        $this->logger->pushHandler($handler);
    }

    /**
     * Pops a handler from the stack
     *
     * @return HandlerInterface|null
     */
    public function popHandler()
    {
        try
        {
            return $this->logger->popHandler();
        }
        catch (Error $ex)
        {
            return null;
        }
        catch (Exception $ex)
        {
            return null;
        }
    }

    /**
     * @return HandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->logger->getHandlers();
    }

    /**
     * Adds a processor on to the stack.
     *
     * @param callable $callback
     * @throws WriteException
     */
    public function pushProcessor(callable $callback)
    {
        try
        {
            $this->logger->pushProcessor($callback);
        }
        catch (Error $ex)
        {
            throw new WriteException("Processor could not be pushed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Processor could not be pushed.", $ex);
        }
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable|null
     */
    public function popProcessor()
    {
        try
        {
            return $this->logger->popProcessor();
        }
        catch (Error $ex)
        {
            return null;
        }
        catch (Exception $ex)
        {
            return null;
        }
    }

    /**
     * @return callable[]
     */
    public function getProcessors()
    {
        return $this->logger->getProcessors();
    }

    /**
     * Adds a log record.
     *
     * @param  integer $level The logging level
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool
     * @throws WriteException
     */
    public function addRecord($level, $message, array $context = array())
    {
        try
        {
            return $this->logger->addRecord($level, $message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record could not be added.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record could not be added.", $ex);
        }
    }

    /**
     * Gets all supported logging levels.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public function getLevels()
    {
        return ${$this->loggerClass}::getLevels();
    }

    /**
     * Gets the name of the logging level.
     *
     * @param  integer $level
     * @return string|null
     */
    public function getLevelName($level)
    {
        try
        {
            return ${$this->loggerClass}::getLevelName($level);
        }
        catch (Error $ex)
        {
            return null;
        }
        catch (Exception $ex)
        {
            return null;
        }
    }

    /**
     * Checks whether the Logger has a handler that listens on the given level
     *
     * @param  integer $level
     * @return Boolean
     */
    public function isHandling($level)
    {
        return $this->logger->isHandling($level);
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  mixed $level The log level
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     * @throws WriteException
     */
    public function log($level, $message, array $context = array())
    {
        try
        {
            return $this->logger->log($level, $message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record with undefined level could not be logged.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record with undefined level could not be logged.", $ex);
        }
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     * @throws WriteException
     */
    public function debug($message, array $context = array())
    {
        try
        {
            return $this->logger->debug($message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     * @throws WriteException
     */
    public function info($message, array $context = array())
    {
        try
        {
            return $this->logger->info($message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record with info level could not be logged.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record with info level could not be logged.", $ex);
        }
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     * @throws WriteException
     */
    public function notice($message, array $context = array())
    {
        try
        {
            return $this->logger->notice($message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record with notice level could not be logged.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record with notice level could not be logged.", $ex);
        }
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     * @throws WriteException
     */
    public function warning($message, array $context = array())
    {
        try
        {
            return $this->logger->warning($message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record with warning level could not be logged.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record with warning level could not be logged.", $ex);
        }
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     * @throws WriteException
     */
    public function error($message, array $context = array())
    {
        try
        {
            return $this->logger->error($message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     * @throws WriteException
     */
    public function critical($message, array $context = array())
    {
        try
        {
            return $this->logger->critical($message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     * @throws WriteException
     */
    public function alert($message, array $context = array())
    {
        try
        {
            return $this->logger->alert($message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     * @throws WriteException
     */
    public function emergency($message, array $context = array())
    {
        try
        {
            return $this->logger->emergency($message, $context);
        }
        catch (Error $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Record with debug level could not be logged.", $ex);
        }
    }
}
