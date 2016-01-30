<?php

namespace Kraken\Bridge\League\Container;

use League\Container\Container;
use League\Container\ContainerInterface;

class LeagueContainerFactory
{
    /**
     * @return ContainerInterface
     */
    public static function create()
    {
        return new Container();
    }
}
