<?php

namespace Kraken\Exception;

abstract class Exception extends \Exception
{
    /**
     * @param string $message
     * @param \Exception $previous
     */
    public function __construct($message = 'Unknown exception', \Exception $previous = null)
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
     * @param \Exception $ex
     * @return string
     */
    public static function toString(\Exception $ex)
    {
        return implode("\n", [
            "",
            self::toExceptionString($ex),
            "\t" . 'stack trace:',
            self::toTraceString($ex)
        ]);
    }

    /**
     * @param \Exception $ex
     * @return mixed
     */
    public static function toTrace(\Exception $ex)
    {
        return ExceptionHelper::getExceptionStack($ex);
    }

    /**
     * @param \Exception $ex
     * @return string[]
     */
    public static function toTraceStack(\Exception $ex)
    {
        $list = [];
        for ($stack = ExceptionHelper::getExceptionStack($ex); $stack !== null; $stack = $stack['prev'])
        {
            $list = array_merge($stack['trace'], $list);
        }

        return $list;
    }

    /**
     * @param \Exception $ex
     * @return string[]
     */
    public static function toExceptionStack(\Exception $ex)
    {
        $list = [];
        for ($stack = ExceptionHelper::getExceptionStack($ex); $stack !== null; $stack = $stack['prev'])
        {
            $list[] = ExceptionHelper::parseMessage($stack);
        }

        return array_reverse($list);
    }

    /**
     * @param \Exception $ex
     * @return string
     */
    public static function toTraceString(\Exception $ex)
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
     * @param \Exception $ex
     * @return string
     */
    public static function toExceptionString(\Exception $ex)
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
