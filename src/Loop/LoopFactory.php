<?php

namespace Kraken\Loop;

use Kraken\Loop\Model\ExtEventLoop;
use Kraken\Loop\Model\LibEventLoop;
use Kraken\Loop\Model\LibEvLoop;
use Kraken\Loop\Model\StreamSelectLoop;
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
            ->define('LibEventLoop', function() {
                return new LibEventLoop();
            })
            ->define('LibEvLoop', function() {
                return new LibEvLoop();
            })
            ->define('ExtEventLoop', function() {
                return new ExtEventLoop();
            })
            ->define('StreamSelectLoop', function() {
                return new StreamSelectLoop();
            })
        ;
    }
}
