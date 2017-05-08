<?php

namespace Kraken\Container;

trait ContainerAwareTrait
{
    /**
     * @var ContainerInterface|null
     */
    protected $container = null;

    /**
     * @see ContainerAwareInterface::setContainer
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @see ContainerAwareInterface::getContainer
     */
    public function getContainer()
    {
        return $this->container;
    }
}
