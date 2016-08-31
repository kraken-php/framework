<?php

namespace Kraken\Runtime\Container;

use Kraken\Runtime\Container\Manager\ProcessManagerBase;
use Kraken\Runtime\Container\Manager\ProcessManagerRemote;
use Kraken\Runtime\Container\Manager\ProcessManagerNull;
use Kraken\Util\Factory\Factory;
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
            ->define(ProcessManagerBase::class, function($config = []) {
                $reflection = (new ReflectionClass(ProcessManagerBase::class));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['system'],
                    $config['filesystem']
                ]);
            })
            ->define(ProcessManagerRemote::class, function($config = []) {
                $reflection = (new ReflectionClass(ProcessManagerRemote::class));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['receiver']
                ]);
            })
            ->define(ProcessManagerNull::class, function($config = []) {
                return new ProcessManagerNull();
            })
        ;
    }
}
