<?php

namespace Kraken\Log;

use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Monolog\Logger as Monolog;

class LoggerWrapper extends Monolog
{
    /**
     * @param string $name The logging channel
     * @param MonologHandlerInterface[] $handlers Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[] $processors Optional array of processors
     */
    public function __construct($name, array $handlers = [], array $processors = [])
    {
        /**
         * This is a fix for Pthreads, since that extension does not copy static variables accross threads
         */
        static::$levels = [
            self::DEBUG     => 'DEBUG',
            self::INFO      => 'INFO',
            self::NOTICE    => 'NOTICE',
            self::WARNING   => 'WARNING',
            self::ERROR     => 'ERROR',
            self::CRITICAL  => 'CRITICAL',
            self::ALERT     => 'ALERT',
            self::EMERGENCY => 'EMERGENCY',
        ];

        parent::__construct($name, $handlers, $processors);
    }
}
