<?php

namespace Kraken\Throwable;

abstract class ThrowableHelper
{
    /**
     * @param string[] $ex
     * @return string
     */
    public static function parseThrowableMessage($ex)
    {
        $array = [
            '[',
            self::getThrowableBasename($ex['class']),
            '] '
        ];
        $message = $ex['message'];

        if (!self::isThrowableError($ex['class']))
        {
            $array = array_merge($array, [
                '"',
                $message,
                '" in ',
                $ex['file'],
                ':',
                $ex['line']
            ]);
        }
        else
        {
            $array[] = $message;
        }

        return implode('', $array);
    }

    /**
     * @param \Error|\Exception $ex
     * @param string[] &$data
     * @param int $offset
     * @return mixed
     */
    public static function getThrowableStack($ex, &$data = [], $offset = 0)
    {
        $data = self::getThrowableData($ex, $offset);

        if (($current = $ex->getPrevious()) !== null)
        {
            self::getThrowableStack($current, $data['prev'], count(self::getTraceElements($ex)));
        }

        return $data;
    }

    /**
     * @param \Error|\Exception $ex
     * @param int $offset
     * @return string[]
     */
    public static function getThrowableData($ex, $offset = 0)
    {
        return [
            'message'   => $ex->getMessage(),
            'class'     => get_class($ex),
            'file'      => $ex->getFile(),
            'line'      => $ex->getLine(),
            'code'      => $ex->getCode(),
            'trace'     => self::getTraceElements($ex, $offset),
            'prev'      => null
        ];
    }

    /**
     * @param \Error|\Exception $ex
     * @param int $offset
     * @return string[]
     */
    protected static function getTraceElements($ex, $offset = 0)
    {
        $trace = $ex->getTrace();
        $elements = [
            '[exception thrown] ' . self::getThrowableBasename(get_class($ex))
        ];

        foreach ($trace as $currentTrack)
        {
            $elements[] = self::parseTraceElement($currentTrack);
        }
        $elements[] = '[main]';

        array_splice($elements, -$offset+1, $offset);

        return $elements;
    }

    /**
     * @param mixed[] $element
     * @return string
     */
    protected static function parseTraceElement($element)
    {
        if (!isset($element['class']))
        {
            $element['class'] = '';
        }

        if (!isset($element['file']))
        {
            $element['file'] = 'unknown';
        }

        if (!isset($element['line']))
        {
            $element['line'] = 0;
        }

        if (!isset($element['type']))
        {
            $element['type'] = '';
        }

        return implode('', [
            '[method call] ',
            $element['class'],
            $element['type'],
            $element['function'],
            '(',
            self::parseArgs($element['args']),
            ') in ',
            basename($element['file']),
            ':',
            $element['line']
        ]);
    }

    /**
     * @param mixed[] $args
     * @return string
     */
    protected static function parseArgs($args)
    {
        $elements = [];

        foreach ($args as $element)
        {
            if (is_array($element))
            {
                $element = 'Array';
            }
            else if (is_object($element))
            {
                $element = get_class($element);
            }
            else if (is_string($element))
            {
                $element = (strlen($element) > 32) ? substr($element, 0, 32) . '...' : $element;
                $element = '"' . $element . '"';
            }

            $elements[] = $element;
        }

        return implode(', ', $elements);
    }

    /**
     * @param string $class
     * @return string
     */
    protected static function getThrowableBasename($class)
    {
        $tmp = explode('\\', $class);
        $className = end($tmp);

        return $className;
    }

    /**
     * @param string $class
     * @return bool
     */
    protected static function isThrowableError($class)
    {
        return in_array($class, [
            'Kraken\Throwable\Interpreter\FatalException',
            'Kraken\Throwable\Interpreter\WarningException',
            'Kraken\Throwable\Interpreter\FatalException'
        ], true);
    }
}
