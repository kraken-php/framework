<?php

namespace Kraken\_Unit\Container\_Provider;

use Kraken\Container\ServiceProvider;

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
