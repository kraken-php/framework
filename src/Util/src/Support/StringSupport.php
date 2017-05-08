<?php

namespace Kraken\Util\Support;

abstract class StringSupport
{
    /**
     * Parametrize string using array of params.
     *
     * @param string $str
     * @param string[] $params
     * @return string
     */
    public static function parametrize($str, $params)
    {
        $keys = array_keys($params);
        $vals = array_values($params);

        array_walk($keys, function(&$key) {
            $key = '%' . $key . '%';
        });

        return str_replace($keys, $vals, $str);
    }

    /**
     * Check if string matches pattern using simple regexp method.
     *
     * @param string $pattern
     * @param string $string
     * @return bool
     */
    public static function match($pattern, $string)
    {
        return fnmatch($pattern, $string);
    }

    /**
     * Filter and return entries that match any of specified patterns.
     *
     * @param string|string[] $pattern
     * @param string[] $entries
     * @return string[]
     */
    public static function find($pattern, $entries)
    {
        if (is_array($pattern))
        {
            return static::findFew($pattern, $entries);
        }

        return static::findOne($pattern, $entries);
    }

    /**
     * Filter and return entries that match specified pattern.
     *
     * @param string $pattern
     * @param string[] $entries
     * @return string[]
     */
    public static function findOne($pattern, $entries)
    {
        $found = [];

        foreach ($entries as $entry)
        {
            if (static::match($pattern, $entry))
            {
                $found[] = $entry;
            }
        }

        return $found;
    }

    /**
     * Filter and return entries that match any of specified patterns.
     *
     * @param string[] $patterns
     * @param string[] $entries
     * @return string[]
     */
    public static function findFew($patterns, $entries)
    {
        $found = [];

        foreach ($patterns as $pattern)
        {
            $found = array_merge($found, static::findOne($pattern, $entries));
        }

        $found = array_unique($found);
        $results = [];

        foreach ($entries as $entry)
        {
            if (in_array($entry, $found, true) === true)
            {
                $results[] = $entry;
            }
        }

        unset($found);

        return $results;
    }
}
