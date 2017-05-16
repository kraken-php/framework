<?php

namespace Kraken\Redis\Command;

use Clue\Redis\Protocol\Model\Request;

class Builder
{
    public static function build($command, $args = [])
    {
        return new Request($command, $args);
    }
}