<?php

namespace Kraken\_Unit\Core\_Provider;

use Kraken\Core\Service\ServiceProvider;

class BProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $requires = [ 'D' ];

    /**
     * @var string[]
     */
    protected $provides = [ 'B' ];
}
