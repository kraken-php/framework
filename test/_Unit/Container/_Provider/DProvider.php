<?php

namespace Kraken\_Unit\Container\_Provider;

use Kraken\Container\ServiceProvider;

class DProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $requires = [];

    /**
     * @var string[]
     */
    protected $provides = [ 'D' ];
}
