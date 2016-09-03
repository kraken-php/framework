<?php

namespace Kraken\_Unit\Container\_Provider;

use Kraken\Container\ServiceProvider;

class GProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $requires = [ 'F' ];

    /**
     * @var string[]
     */
    protected $provides = [ 'G' ];
}
