<?php

namespace Kraken\Pattern\Enum;

interface EnumInterface
{
    /**
     * Checks if Enum class has defined const with given value
     *
     * @param mixed $value
     * @return bool
     */
    public static function isSupported($value);

    /**
     * Returns all const defined inside class
     *
     * @return array
     */
    public static function getSupported();
}
