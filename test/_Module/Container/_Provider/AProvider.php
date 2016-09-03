<?php

namespace Kraken\_Module\Container\_Provider;

use Kraken\_Module\Container\_Resource\Resource;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;

class AProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $requires = [];

    /**
     * @var string[]
     */
    protected $provides = [
        Resource::class
    ];

    /**
     * @param ContainerInterface $container
     */
    public function register(ContainerInterface $container)
    {
        $container->instance(Resource::class, new Resource([ 'a' => 'A', 'b' => 'B', 'booted' => false ]));
    }

    /**
     * @param ContainerInterface $container
     */
    public function unregister(ContainerInterface $container)
    {
        $container->remove(Resource::class);
    }

    /**
     * @param ContainerInterface $container
     */
    public function boot(ContainerInterface $container)
    {
        $res = $container->make(Resource::class);

        $res->data['booted'] = true;
    }
}
