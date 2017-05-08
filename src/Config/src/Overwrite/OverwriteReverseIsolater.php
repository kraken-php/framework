<?php

namespace Kraken\Config\Overwrite;

class OverwriteReverseIsolater
{
    /**
     * @param array $old
     * @param array $new
     * @return array
     */
    public function __invoke($old, $new)
    {
        return $old;
    }
}
