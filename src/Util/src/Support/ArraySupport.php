<?php

namespace Kraken\Util\Support;

abstract class ArraySupport
{
    /**
     * Check if given array is empty.
     *
     * @param array $array
     * @return bool
     */
    public static function isEmpty($array)
    {
        return empty($array);
    }

    /**
     * Check if given key exists in array with dot notation support.
     *
     * @param array $array
     * @param string $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        $key = static::normalizeKey($key);

        if ($key === null || $key === '' || static::isEmpty($array))
        {
            return false;
        }

        $keys = explode('.', $key);
        $currentElement = $array;

        foreach ($keys as $currentKey)
        {
            if (!is_array($currentElement) || !array_key_exists($currentKey, $currentElement))
            {
                return false;
            }

            $currentElement = $currentElement[(string) $currentKey];
        }

        return true;
    }

    /**
     * Return the value stored under given key in the array with dot notation support.
     *
     * @param string $key
     * @param array $array
     * @param mixed $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        $key = static::normalizeKey($key);

        if ($key === null || $key === '')
        {
            return $array;
        }

        $keys = explode('.', $key);
        $currentElement = $array;

        foreach ($keys as $currentKey)
        {
            if (!is_array($currentElement) || !array_key_exists($currentKey, $currentElement))
            {
                return $default;
            }

            $currentElement = $currentElement[(string) $currentKey];
        }

        return $currentElement;
    }

    /**
     * Set the value for given key in the array with dot notation support.
     *
     * @param array &$array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        $key = static::normalizeKey($key);

        if ($key === null || $key === '')
        {
            return ($array = $value);
        }

        $keys = explode('.', $key);
        $last = array_pop($keys);
        $currentElement =& $array;

        foreach ($keys as $currentKey)
        {
            if (!array_key_exists($currentKey, $currentElement) || !is_array($currentElement[$currentKey]))
            {
                $currentElement[$currentKey] = [];
            }

            $currentElement =& $currentElement[$currentKey];
        }

        $currentElement[$last] = $value;

        return $array;
    }

    /**
     * Remove the value stored under given key from the array with dot notation support.
     *
     * @param array &$array
     * @param string $key
     * @return bool
     */
    public static function remove(&$array, $key)
    {
        $key = static::normalizeKey($key);

        if ($key === null || $key === '')
        {
            return ($array = []);
        }

        $keys = explode('.', $key);
        $last = array_pop($keys);
        $currentElement =& $array;

        foreach ($keys as $currentKey)
        {
            if (!array_key_exists($currentKey, $currentElement) || !is_array($currentElement[$currentKey]))
            {
                $currentElement[$currentKey] = [];
            }

            $currentElement =& $currentElement[$currentKey];
        }

        unset($currentElement[$last]);

        return $array;
    }

    /**
     * Flatten a multi-dimensional array into a single level using dot notation.
     *
     * @param array $array
     * @return array
     */
    public static function flatten($array)
    {
        return static::flattenRecursive($array, '');
    }

    /**
     * Expand flattened array into a multi-dimensional one.
     *
     * @param $array
     * @return array
     */
    public static function expand($array)
    {
        $multiArray = [];

        foreach ($array as $key=>&$value)
        {
            $keys = explode('.', $key);
            $lastKey = array_pop($keys);
            $currentPointer = &$multiArray;

            foreach ($keys as $currentKey)
            {
                if (!isset($currentPointer[$currentKey]))
                {
                    $currentPointer[$currentKey] = [];
                }

                $currentPointer = &$currentPointer[$currentKey];
            }

            $currentPointer[$lastKey] = $value;
        }

        return $multiArray;
    }

    /**
     * Merge several arrays, preserving dot notation.
     *
     * @param array[] $arrays
     * @return array
     */
    public static function merge($arrays)
    {
        $merged = [];

        foreach ($arrays as $array)
        {
            $merged = array_merge($merged, static::flatten($array));
        }

        return static::expand($merged);
    }

    /**
     * Merge several arrays.
     *
     * @param array[] $arrays
     * @return array
     */
    public static function replace($arrays)
    {
        $merged = [];

        foreach ($arrays as $array)
        {
            $merged = array_merge($merged, $array);
        }

        return $merged;
    }

    /**
     * Normalize key to dot notation valid format.
     *
     * @param string $key
     * @return string
     */
    public static function normalizeKey($key)
    {
        return ($key === null) ? null : trim(
            str_replace(
                [ " ", "\t", "\n", "\r", "\0", "\x0B" ], [ '', '', '', '', '', '' ], $key
            ), '.'
        );
    }

    /**
     * Flatten a single recursion of array.
     *
     * @param array $recursion
     * @param string $prefix
     * @return array
     */
    protected static function flattenRecursive(&$recursion, $prefix)
    {
        $values = [];

        foreach ($recursion as $key=>&$value)
        {
            if (is_array($value) && !empty($value))
            {
                $values = array_merge($values, static::flattenRecursive($value, $prefix . $key . '.'));
            }
            else
            {
                $values[$prefix . $key] = $value;
            }
        }

        return $values;
    }
}
