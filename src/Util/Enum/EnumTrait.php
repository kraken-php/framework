<?php

namespace Kraken\Util\Enum;

trait EnumTrait
{
    /**
     * @see EnumInterface::isSupported
     */
    public static function isSupported($value)
    {
        return in_array($value, static::getSupported(), true);
    }

    /**
     * @see EnumInterface::getSupported
     */
    public static function getSupported()
    {
        return (new \ReflectionClass(__CLASS__))->getConstants();
    }
}
