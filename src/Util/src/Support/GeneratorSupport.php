<?php

namespace Kraken\Util\Support;

abstract class GeneratorSupport
{
    /**
     * Return uniqid prefixed with given string.
     *
     * @param string $name
     * @return string
     */
    public static function genId($name)
    {
        return uniqid($name, true);
    }
}
