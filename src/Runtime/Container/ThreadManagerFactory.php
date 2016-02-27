<?php

namespace Kraken\Runtime\Container;

use Kraken\Util\Factory\Factory;
use Kraken\Runtime\Container\Manager\ThreadManagerNull;
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
            ->define('Kraken\Runtime\Container\Manager\ThreadManagerBase', function($config) {
                $reflection = (new ReflectionClass('Kraken\Runtime\Container\Manager\ThreadManagerBase'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel']
                ]);
            })
            ->define('Kraken\Runtime\Container\Manager\ThreadManagerRemote', function($config) {
                $reflection = (new ReflectionClass('Kraken\Runtime\Container\Manager\ThreadManagerRemote'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['receiver']
                ]);
            })
            ->define('Kraken\Runtime\Container\Manager\ThreadManagerNull', function($config) {
                return new ThreadManagerNull();
            })
        ;
    }
}
