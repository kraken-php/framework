<?php

namespace Kraken\Throwable;

abstract class ExceptionHandler
{
    /**
     * @var string
     */
    protected static $handler = '\Kraken\Throwable\Exception::toString';

    /**
     * Invoke default Exception Handler.
     *
     * @param \Error|\Exception $ex
     */
    public static function handleException($ex)
    {
        echo call_user_func(static::$handler, $ex);
    }
}
