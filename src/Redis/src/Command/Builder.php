<?php

namespace Kraken\Redis\Command;

use Kraken\Redis\Protocol\Data\Request;

class Builder
{
    public static function build($command, $args = [])
    {
        return new Request($command, $args);
    }
}