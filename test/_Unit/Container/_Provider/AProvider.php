<?php

namespace Kraken\_Unit\Container\_Provider;

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
    protected $provides = [ 'A0', 'A1', 'A2' ];
}
