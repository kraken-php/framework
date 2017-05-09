<?php

namespace Kraken\Util\Support;

abstract class TimeSupport
{
    /**
     * Return timestamp for now.
     *
     * @return float
     */
    public static function now()
    {
        return round(microtime(true)*1000);
    }
}
