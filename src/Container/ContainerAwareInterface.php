<?php

namespace Kraken\Container;

interface ContainerAwareInterface
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function setContainer(ContainerInterface $container);

    /**
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * @return ContainerInterface
     */
    public function container();
}
