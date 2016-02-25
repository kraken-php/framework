<?php

namespace Kraken\Throwable;

abstract class Exception extends \Exception
{
    /**
     * @param string $message
     * @param \Error|\Exception $previous
     */
    public function __construct($message = 'Unknown exception', $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::toString($this);
    }

    /**
     * @param \Error|\Exception $ex
     * @return string
     */
    public static function toString($ex)
    {
        return implode("\n", [
            "",
            self::toExceptionString($ex),
            "\t" . 'stack trace:',
            self::toTraceString($ex)
        ]);
    }

    /**
     * @param \Error|\Exception $ex
     * @return mixed
     */
    public static function toTrace($ex)
    {
        return ThrowableHelper::getThrowableStack($ex);
    }

    /**
     * @param \Error|\Exception $ex
     * @return string[]
     */
    public static function toTraceStack($ex)
    {
        $list = [];
        for ($stack = ThrowableHelper::getThrowableStack($ex); $stack !== null; $stack = $stack['prev'])
        {
            $list = array_merge($stack['trace'], $list);
        }

        return $list;
    }

    /**
     * @param \Error|\Exception $ex
     * @return string[]
     */
    public static function toExceptionStack($ex)
    {
        $list = [];
        for ($stack = ThrowableHelper::getThrowableStack($ex); $stack !== null; $stack = $stack['prev'])
        {
            $list[] = ThrowableHelper::parseThrowableMessage($stack);
        }

        return array_reverse($list);
    }

    /**
     * @param \Error|\Exception $ex
     * @return string
     */
    public static function toTraceString($ex)
    {
        $stack = [];
        $i = 0;
        foreach (self::toTraceStack($ex) as $element)
        {
            $stack[] = "\t#" . $i . ' ' . $element;
            ++$i;
        }

        return implode("\n", $stack);
    }

    /**
     * @param \Error|\Exception $ex
     * @return string
     */
    public static function toExceptionString($ex)
    {
        $stack = [];
        $i = 0;
        foreach (self::toExceptionStack($ex) as $element)
        {
            $stack[] = "\t#" . $i . ' ' . $element;
            ++$i;
        }

        return implode("\n", $stack);
    }
}
