<?php

namespace Kraken\_Unit\Container\_Mock;

use Kraken\Container\Container;
use Kraken\Container\Model\ContainerModel;
use Kraken\Container\Model\ContainerReflection;

class ContainerMock extends Container
{
    /**
     * @param ContainerModel $container
     * @param ContainerReflection $reflector
     */
    public function __construct(ContainerModel $container, ContainerReflection $reflector)
    {
        $this->container = $container;
        $this->reflector = $reflector;
        $this->container->delegate($this->reflector);
    }

    /**
     * @return ContainerModel
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return ContainerReflection
     */
    public function getReflector()
    {
        return $this->reflector;
    }
}
