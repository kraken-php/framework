<?php

namespace Kraken\_Unit\Core\_Provider;

use Kraken\Core\Service\ServiceProvider;

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
