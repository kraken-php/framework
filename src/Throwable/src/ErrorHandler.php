<?php

namespace Kraken\Throwable;

use Kraken\Throwable\Error\FatalError;
use Kraken\Throwable\Error\NoticeError;
use Kraken\Throwable\Error\WarningError;

abstract class ErrorHandler
{
    /**
     * @var int
     */
    const E_UNSUPPORTED = 8;

    /**
     * @var int
     */
    const E_ERROR = 4;

    /**
     * @var int
     */
    const E_WARNING = 2;

    /**
     * @var int
     */
    const E_NOTICE = 1;

    /**
     * @var string
     */
    protected static $errHandler = '\Kraken\Throwable\Error::toString';

    /**
     * @var string
     */
    protected static $excHandler = '\Kraken\Throwable\Exception::toString';

    /**
     * Invoke default Error Handler.
     *
     * @param int $code
     * @param string $message
     * @param string $file
     * @param int $line
     * @throws FatalError
     * @throws NoticeError
     * @throws WarningError
     */
    public static function handleError($code, $message, $file, $line)
    {
        $list = static::getSystemError($code);
        $name = $list[0];
        $type = $list[1];

        $message = "\"$message\" in $file:$line";

        switch ($type)
        {
            case static::E_NOTICE:  throw new NoticeError($message);
            case static::E_WARNING: throw new WarningError($message);
            case static::E_ERROR:   throw new FatalError($message);
            default:                return;
        }
    }

    /**
     * Invoke default Shutdown Handler.
     *
     * @param bool $forceKill
     */
    public static function handleShutdown($forceKill = false)
    {
        $err = error_get_last();

        try
        {
            static::handleError($err['type'], $err['message'], $err['file'], $err['line']);
        }
        catch (\Error $ex)
        {
            echo call_user_func(static::$errHandler, $ex) . PHP_EOL;
        }
        catch (\Exception $ex)
        {
            echo call_user_func(static::$excHandler, $ex) . PHP_EOL;
        }

        if ($forceKill)
        {
            posix_kill(posix_getpid(), 9);
        }
    }

    /**
     * @param int $type
     * @return array
     */
    private static function getSystemError($type)
    {
        switch($type)
        {
            case E_ERROR: // 1 //
                return [ 'E_ERROR',             static::E_ERROR ];

            case E_WARNING: // 2 //
                return [ 'E_WARNING',           static::E_WARNING ];

            case E_PARSE: // 4 //
                return [ 'E_PARSE',             static::E_ERROR ];

            case E_NOTICE: // 8 //
                return [ 'E_NOTICE',            static::E_NOTICE ];

            case E_CORE_ERROR: // 16 //
                return [ 'E_CORE_ERROR',        static::E_ERROR ];

            case E_CORE_WARNING: // 32 //
                return [ 'E_CORE_WARNING',      static::E_WARNING ];

            case E_COMPILE_ERROR: // 64 //
                return [ 'E_COMPILE_ERROR',     static::E_ERROR ];

            case E_COMPILE_WARNING: // 128 //
                return [ 'E_COMPILE_WARNING',   static::E_WARNING ];

            case E_USER_ERROR: // 256 //
                return [ 'E_USER_ERROR',        static::E_ERROR ];

            case E_USER_WARNING: // 512 //
                return [ 'E_USER_WARNING',      static::E_WARNING ];

            case E_USER_NOTICE: // 1024 //
                return [ 'E_USER_NOTICE',       static::E_NOTICE ];

            case E_STRICT: // 2048 //
                return [ 'E_STRICT',            static::E_ERROR ];

            case E_RECOVERABLE_ERROR: // 4096 //
                return [ 'E_RECOVERABLE_ERROR', static::E_WARNING ];

            case E_DEPRECATED: // 8192 //
                return [ 'E_DEPRECATED',        static::E_NOTICE ];

            case E_USER_DEPRECATED: // 16384 //
                return [ 'E_USER_DEPRECATED',   static::E_NOTICE ];

            default:
                return [ 'E_UNKNOWN',           static::E_UNSUPPORTED ];
        }
    }
}
