<?php

namespace Kraken\Throwable;

abstract class Error extends \Error
{
    /**
     * @param string $message
     * @param \Error $previous
     */
    public function __construct($message = 'Unknown exception', \Error $previous = null)
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
     * @param \Error $ex
     * @return string
     */
    public static function toString(\Error $ex)
    {
        return implode("\n", [
            "",
            self::toExceptionString($ex),
            "\t" . 'stack trace:',
            self::toTraceString($ex)
        ]);
    }

    /**
     * @param \Error $ex
     * @return mixed
     */
    public static function toTrace(\Error $ex)
    {
        return ErrorHelper::getErrorStack($ex);
    }

    /**
     * @param \Error $ex
     * @return string[]
     */
    public static function toTraceStack(\Error $ex)
    {
        $list = [];
        for ($stack = ErrorHelper::getErrorStack($ex); $stack !== null; $stack = $stack['prev'])
        {
            $list = array_merge($stack['trace'], $list);
        }

        return $list;
    }

    /**
     * @param \Error $ex
     * @return string[]
     */
    public static function toExceptionStack(\Error $ex)
    {
        $list = [];
        for ($stack = ErrorHelper::getErrorStack($ex); $stack !== null; $stack = $stack['prev'])
        {
            $list[] = ErrorHelper::parseMessage($stack);
        }

        return array_reverse($list);
    }

    /**
     * @param \Error $ex
     * @return string
     */
    public static function toTraceString(\Error $ex)
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
     * @param \Error $ex
     * @return string
     */
    public static function toExceptionString(\Error $ex)
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
