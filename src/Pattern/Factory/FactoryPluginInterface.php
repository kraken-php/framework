<?php

namespace Kraken\Pattern\Factory;

use Kraken\Throwable\Runtime\ExecutionException;

interface FactoryPluginInterface
{
    /**
     * @param FactoryInterface $factory
     * @throws ExecutionException
     */
    public function registerPlugin(FactoryInterface $factory);

    /**
     * @param FactoryInterface $factory
     */
    public function unregisterPlugin(FactoryInterface $factory);
}
