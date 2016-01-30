<?php

namespace Kraken\Runtime\Process;

use Kraken\Pattern\Factory\Factory;
use Kraken\Runtime\Process\Manager\ProcessManagerNull;
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
            ->define('Kraken\Runtime\Process\Manager\ProcessManagerBase', function($config) {
                $reflection = (new ReflectionClass('Kraken\Runtime\Process\Manager\ProcessManagerBase'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['env'],
                    $config['system'],
                    $config['filesystem']
                ]);
            })
            ->define('Kraken\Runtime\Process\Manager\ProcessManagerRemote', function($config) {
                $reflection = (new ReflectionClass('Kraken\Runtime\Process\Manager\ProcessManagerRemote'));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['receiver']
                ]);
            })
            ->define('Kraken\Runtime\Process\Manager\ProcessManagerNull', function($config) {
                return new ProcessManagerNull();
            })
        ;
    }
}
