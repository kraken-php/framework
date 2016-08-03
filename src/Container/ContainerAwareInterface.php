<?php

namespace Kraken\Container;

interface ContainerAwareInterface
{
    /**
     * Set Container of which object is aware of.
     *
     * @param ContainerInterface|null $container
     * @return mixed
     */
    public function setContainer(ContainerInterface $container = null);

    /**
     * Get Container of which object is aware of.
     *
     * @return ContainerInterface|null
     */
    public function getContainer();
}
