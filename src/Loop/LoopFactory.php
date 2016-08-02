<?php

namespace Kraken\Loop;

use Kraken\Loop\Model\SelectLoop;
use Kraken\Util\Factory\Factory;
use Kraken\Util\Factory\FactoryInterface;

class LoopFactory extends Factory implements FactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->define('SelectLoop', function() {
                return new SelectLoop();
            })
        ;
    }
}
