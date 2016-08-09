<?php

namespace Kraken\_Module\Core\_Provider;

use Kraken\_Module\Core\_Resource\Resource;
use Kraken\_Module\Core\_Resource\ResourceInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;

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
     * @param CoreInterface $core
     */
    public function register(CoreInterface $core)
    {
        $core->alias(ResourceInterface::class, Resource::class);
    }

    /**
     * @param CoreInterface $core
     */
    public function unregister(CoreInterface $core)
    {
        $core->remove(ResourceInterface::class);
    }
}
