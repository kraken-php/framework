<?php

namespace Kraken\Log;

use Kraken\Log\Handler\HandlerInterface;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;
use Kraken\Util\Enum\EnumInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

interface LoggerInterface extends EnumInterface, PsrLoggerInterface
{
    /**
     * Return name of logger.
     *
     * @return string
     */
    public function getName();

    /**
     * Push handler on to the stack.
     *
     * @param HandlerInterface $handler
     */
    public function pushHandler(HandlerInterface $handler);

    /**
     * Pop handler from the stack.
     *
     * @return HandlerInterface|null
     */
    public function popHandler();

    /**
     * Get all handlers from the stack.
     *
     * @return HandlerInterface[]
     */
    public function getHandlers();

    /**
     * Add processor on to the stack.
     *
     * @param callable $callback
     * @throws IoWriteException
     */
    public function pushProcessor(callable $callback);

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable|null
     */
    public function popProcessor();

    /**
     * Get all processors from the stack.
     *
     * @return callable[]
     */
    public function getProcessors();

    /**
     * Get all supported logging levels.
     *
     * @return int[]
     */
    public function getLevels();

    /**
     * Get the name of the logging level.
     *
     * @param int $level
     * @return string|null
     */
    public function getLevelName($level);

    /**
     * Check whether the Logger has a handler that listens on the given level.
     *
     * @param int $level
     * @return bool
     */
    public function isHandling($level);
}
