<?php

namespace Kraken\_Unit\Core\_Provider;

use Kraken\Core\Service\ServiceProvider;

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
