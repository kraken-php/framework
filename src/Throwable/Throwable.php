<?php

namespace Kraken\Throwable;

abstract class Throwable
{
    /**
     * Parse Throwable message to proper format.
     *
     * @param string[] $ex
     * @return string
     */
    public static function parseThrowableMessage($ex)
    {
        $message = $ex['message'];

        if ($ex['isError'] && strpos($message, ' in ') !== false)
        {
            $message = preg_replace('#([a-zA-Z0-9-_]+?)/#siU', '', $message);
            $message = preg_replace('#/#si', '', $message, 1);
        }
        else
        {
            $message = trim($message, '"');
            $file = str_replace('.php', '', basename($ex['file']));
            $line = $ex['line'];
            $message = '"' . $message . '" in ' . $file . ':' . $line;
        }

        return '[' . static::getBasename($ex['class']) . '] ' . $message;
    }

    /**
     * Return throwable stack in recursive array format.
     *
     * @param \Error|\Exception $ex
     * @param string[] &$data
     * @param int $offset
     * @return mixed
     */
    public static function getThrowableStack($ex, &$data = [], $offset = 0)
    {
        $data = static::getThrowableData($ex, $offset);

        if (($current = $ex->getPrevious()) !== null)
        {
            static::getThrowableStack($current, $data['prev'], count(static::getTraceElements($ex)));
        }

        return $data;
    }

    /**
     * Return throwable data in array format.
     *
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
            'trace'     => static::getTraceElements($ex, $offset),
            'isError'   => $ex instanceof \Error,
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
        $file  = str_replace('.php', '', basename($ex->getFile()));
        $elements = [
            '[throwable] ' . get_class($ex) . '(...) in ' . $file .':' . $ex->getLine()
        ];

        foreach ($trace as $currentTrack)
        {
            $elements[] = static::parseTraceElement($currentTrack);
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
        $element['class']    = isset($element['class'])    ? $element['class']    : 'Undefined';
        $element['file']     = isset($element['file'])     ? $element['file']     : 'unknown';
        $element['line']     = isset($element['line'])     ? $element['line']     : 0;
        $element['type']     = isset($element['type'])     ? $element['type']     : '';
        $element['function'] = isset($element['function']) ? $element['function'] : '::undefined';
        $element['args']     = isset($element['args'])     ? $element['args']     : [];

        return implode('', [
            '[call] ',
            $element['class'],
            $element['type'],
            $element['function'],
            '(',
            static::parseArgs($element['args']),
            ') in ',
            str_replace('.php', '', basename($element['file'])),
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
    protected static function getBasename($class)
    {
        $tmp = explode('\\', $class);
        $className = end($tmp);

        return $className;
    }
}
