<?php

namespace Kraken\Support;

abstract class GeneratorSupport
{
    /**
     * @param string $name
     * @return string
     */
    public static function genId($name)
    {
        return uniqid($name, true);
    }
}
