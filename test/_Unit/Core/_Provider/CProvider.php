<?php

namespace Kraken\_Unit\Core\_Provider;

use Kraken\Container\ServiceProvider;

class CProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $requires = [ 'D' ];

    /**
     * @var string[]
     */
    protected $provides = [];
}
