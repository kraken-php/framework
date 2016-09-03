<?php

namespace Kraken\_Unit\Container\_Provider;

use Kraken\Container\ServiceProvider;

class EProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $requires = [ 'A1' ];

    /**
     * @var string[]
     */
    protected $provides = [ 'E' ];
}
