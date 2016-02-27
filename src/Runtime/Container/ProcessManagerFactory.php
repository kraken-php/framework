<?php

namespace Kraken\Runtime\Container;

use Kraken\Util\Factory\Factory;
use Kraken\Runtime\Container\Manager\ProcessManagerNull;
use ReflectionClass;

class ProcessManagerFactory extends Factory implements ProcessManagerFactoryInterface
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->define('Kraken\Runtime\Container\Manager\ProcessManagerBase', function($config) {
                $reflection = (new ReflectionClass('Kraken\Runtime\Container\Manager\ProcessManagerBase'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['env'],
                    $config['system'],
                    $config['filesystem']
                ]);
            })
            ->define('Kraken\Runtime\Container\Manager\ProcessManagerRemote', function($config) {
                $reflection = (new ReflectionClass('Kraken\Runtime\Container\Manager\ProcessManagerRemote'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['receiver']
                ]);
            })
            ->define('Kraken\Runtime\Container\Manager\ProcessManagerNull', function($config) {
                return new ProcessManagerNull();
            })
        ;
    }
}
