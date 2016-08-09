<?php

namespace Kraken\_Unit\Core\_Provider;

use Kraken\Core\Service\ServiceProvider;

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
