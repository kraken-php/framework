<?php

namespace Kraken\Throwable;

class Exception extends \Exception
{
    /**
     * @param string $message
     * @param \Error|\Exception|null $previous
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
        return static::toString($this);
    }

    /**
     * Return Exception full trace in string format.
     *
     * @param \Error|\Exception $ex
     * @return string
     */
    public static function toString($ex)
    {
        return implode("\n", [
            "\t" . 'Throwable trace:',
            static::toThrowableString($ex),
            "\t" . 'Stack trace:',
            static::toStackString($ex)
        ]);
    }

    /**
     * Return Exception full trace in array format.
     *
     * @param \Error|\Exception $ex
     * @return mixed
     */
    public static function toTrace($ex)
    {
        return Throwable::getThrowableStack($ex);
    }

    /**
     * Return Exception stack trace in array format.
     *
     * @param \Error|\Exception $ex
     * @return string[]
     */
    public static function toStackTrace($ex)
    {
        $list = [];
        for ($stack = Throwable::getThrowableStack($ex); $stack !== null; $stack = $stack['prev'])
        {
            $list = array_merge($stack['trace'], $list);
        }

        return $list;
    }

    /**
     * Return Exception throwable trace in array format.
     *
     * @param \Error|\Exception $ex
     * @return string[]
     */
    public static function toThrowableTrace($ex)
    {
        $list = [];
        for ($stack = Throwable::getThrowableStack($ex); $stack !== null; $stack = $stack['prev'])
        {
            $list[] = Throwable::parseThrowableMessage($stack);
        }

        return array_reverse($list);
    }

    /**
     * Return Exception stack trace in string format.
     *
     * @param \Error|\Exception $ex
     * @return string
     */
    public static function toStackString($ex)
    {
        $stack = [];
        $i = 0;
        $trace = static::toStackTrace($ex);
        $pad = strlen(count($trace)) > 2 ?: 2;

        foreach ($trace as $element)
        {
            $stack[] = "\t" . str_pad('' . $i, $pad, ' ', STR_PAD_LEFT) . '. ' . $element;
            ++$i;
        }

        return implode("\n", $stack);
    }

    /**
     * Return Exception throwable trace in string format.
     *
     * @param \Error|\Exception $ex
     * @return string
     */
    public static function toThrowableString($ex)
    {
        $stack = [];
        $i = 0;
        $trace = static::toThrowableTrace($ex);
        $pad = strlen(count($trace)) > 2 ?: 2;

        foreach ($trace as $element)
        {
            $stack[] = "\t" . str_pad('' . $i, $pad, ' ', STR_PAD_LEFT) . '. ' . $element;
            ++$i;
        }

        return implode("\n", $stack);
    }
}
