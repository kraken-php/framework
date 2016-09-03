<?php

namespace Kraken\_Module\Container\_Provider;

use Kraken\_Module\Container\_Resource\Resource;
use Kraken\_Module\Container\_Resource\ResourceInterface;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;

class BProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $requires = [
        Resource::class
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        ResourceInterface::class
    ];

    /**
     * @param ContainerInterface $container
     */
    public function register(ContainerInterface $container)
    {
        $container->alias(ResourceInterface::class, Resource::class);
    }

    /**
     * @param ContainerInterface $container
     */
    public function unregister(ContainerInterface $container)
    {
        $container->remove(ResourceInterface::class);
    }
}
