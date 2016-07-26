<?php

namespace Kraken\Support;

abstract class StringSupport
{
    /**
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
     * @param string $pattern
     * @param string $string
     * @return bool
     */
    public static function match($pattern, $string)
    {
        return fnmatch($pattern, $string);
    }

    /**
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
