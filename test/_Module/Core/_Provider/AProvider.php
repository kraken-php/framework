<?php

namespace Kraken\_Module\Core\_Provider;

use Kraken\_Module\Core\_Resource\Resource;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;

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
     * @param CoreInterface $core
     */
    public function register(CoreInterface $core)
    {
        $core->instance(Resource::class, new Resource([ 'a' => 'A', 'b' => 'B', 'booted' => false ]));
    }

    /**
     * @param CoreInterface $core
     */
    public function unregister(CoreInterface $core)
    {
        $core->remove(Resource::class);
    }

    /**
     * @param CoreInterface $core
     */
    public function boot(CoreInterface $core)
    {
        $res = $core->make(Resource::class);

        $res->data['booted'] = true;
    }
}
