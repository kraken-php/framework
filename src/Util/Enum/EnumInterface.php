<?php

namespace Kraken\Util\Enum;

interface EnumInterface
{
    /**
     * Check if Enum class has defined const with given value.
     *
     * @param mixed $value
     * @return bool
     */
    public static function isSupported($value);

    /**
     * Return all const defined inside class.
     *
     * @return mixed[]
     */
    public static function getSupported();
}
