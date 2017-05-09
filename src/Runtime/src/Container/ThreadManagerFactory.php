<?php

namespace Kraken\Runtime\Container;

use Kraken\Runtime\Container\Manager\ThreadManagerBase;
use Kraken\Runtime\Container\Manager\ThreadManagerRemote;
use Kraken\Runtime\Container\Manager\ThreadManagerNull;
use Kraken\Util\Factory\Factory;
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
            ->define(ThreadManagerBase::class, function($config = []) {
                $reflection = (new ReflectionClass(ThreadManagerBase::class));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['context']
                ]);
            })
            ->define(ThreadManagerRemote::class, function($config = []) {
                $reflection = (new ReflectionClass(ThreadManagerRemote::class));
                return $reflection->newInstanceArgs([
                    $config['runtime'],
                    $config['channel'],
                    $config['receiver']
                ]);
            })
            ->define(ThreadManagerNull::class, function($config = []) {
                return new ThreadManagerNull();
            })
        ;
    }
}
