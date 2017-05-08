<?php

namespace Kraken\Container\Model;

use League\Container\Container as LeagueContainer;

class ContainerModel extends LeagueContainer
{
    /**
     * Remove existing definitions.
     *
     * @param string $alias
     */
    public function remove($alias)
    {
        unset($this->definitions[$alias]);
        unset($this->sharedDefinitions[$alias]);
        unset($this->shared[$alias]);
    }
}
