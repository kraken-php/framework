<?php

namespace Kraken\Throwable;

use Error;

abstract class ErrorHelper
{
    /**
     * @param string[] $ex
     * @return string
     */
    public static function parseMessage($ex)
    {
        $array = [
            '[',
            self::getErrorBasename($ex['class']),
            '] '
        ];
        $message = $ex['message'];

        if (!self::isErrorError($ex['class']))
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
     * @param Error $ex
     * @param string[] &$data
     * @param int $offset
     * @return mixed
     */
    public static function getErrorStack(\Error $ex, &$data = [], $offset = 0)
    {
        $data = self::getErrorData($ex, $offset);

        if (($current = $ex->getPrevious()) !== null)
        {
            self::getErrorStack($current, $data['prev'], count(self::getTraceElements($ex)));
        }

        return $data;
    }

    /**
     * @param Error $ex
     * @param int $offset
     * @return string[]
     */
    public static function getErrorData(Error $ex, $offset = 0)
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
     * @param Error $ex
     * @param int $offset
     * @return string[]
     */
    protected static function getTraceElements(Error $ex, $offset = 0)
    {
        $trace = $ex->getTrace();
        $elements = [
            '[exception thrown] ' . self::getErrorBasename(get_class($ex))
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
    protected static function getErrorBasename($class)
    {
        $tmp = explode('\\', $class);
        $className = end($tmp);

        return $className;
    }

    /**
     * @param string $class
     * @return bool
     */
    protected static function isErrorError($class)
    {
        return in_array($class, [
            'Kraken\Throwable\Interpreter\FatalException',
            'Kraken\Throwable\Interpreter\WarningException',
            'Kraken\Throwable\Interpreter\FatalException'
        ], true);
    }
}
