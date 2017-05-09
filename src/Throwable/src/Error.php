<?php

namespace Kraken\Throwable;

class Error extends \Error
{
    /**
     * @param string $message
     * @param \Error|\Exception|null $previous
     */
    public function __construct($message = 'Unknown error', $previous = null)
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
     * Return Error full trace in string format.
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
     * Return Error full trace in array format.
     *
     * @param \Error|\Exception $ex
     * @return mixed
     */
    public static function toTrace($ex)
    {
        return Throwable::getThrowableStack($ex);
    }

    /**
     * Return Error stack trace in array format.
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
     * Return Error throwable trace in array format.
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
     * Return Error stack trace in string format.
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
     * Return Error throwable trace in string format.
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
