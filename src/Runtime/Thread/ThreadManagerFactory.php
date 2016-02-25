<?php

namespace Kraken\Runtime\Thread;

use Kraken\Util\Factory\Factory;
use Kraken\Runtime\Thread\Manager\ThreadManagerNull;
use ReflectionClass;

class ThreadManagerFactory extends Factory implements ThreadManagerFactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->define('Kraken\Runtime\Thread\Manager\ThreadManagerBase', function($config) {
                $reflection = (new ReflectionClass('Kraken\Runtime\Thread\Manager\ThreadManagerBase'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel']
                ]);
            })
            ->define('Kraken\Runtime\Thread\Manager\ThreadManagerRemote', function($config) {
                $reflection = (new ReflectionClass('Kraken\Runtime\Thread\Manager\ThreadManagerRemote'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['receiver']
                ]);
            })
            ->define('Kraken\Runtime\Thread\Manager\ThreadManagerNull', function($config) {
                return new ThreadManagerNull();
            })
        ;
    }
}
