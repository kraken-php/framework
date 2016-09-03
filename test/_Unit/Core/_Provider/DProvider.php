<?php

namespace Kraken\_Unit\Core\_Provider;

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
