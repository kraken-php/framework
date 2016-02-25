<?php

namespace Kraken\Util\Enum;

trait EnumTrait
{
    /**
     * @param mixed $value
     * @return bool
     */
    public static function isSupported($value)
    {
        return in_array($value, static::getSupported(), true);
    }

    /**
     * @return array
     */
    public static function getSupported()
    {
        $reflection = new \ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
