<?php

namespace Kraken\Bridge\League\Container;

use React\EventLoop\LoopInterface;

interface LeagueContainerAwareInterface
{
    /**
     * Returns instance of original league container
     *
     * @return LoopInterface
     */
    public function getLeagueContainer();
}
