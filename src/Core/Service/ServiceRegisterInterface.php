<?php

namespace Kraken\Core\Service;

use Kraken\Throwable\Runtime\ExecutionException;
use Kraken\Throwable\Runtime\InvalidArgumentException;
use Kraken\Throwable\Resource\ResourceDefinedException;
use Kraken\Throwable\Resource\ResourceUndefinedException;

interface ServiceRegisterInterface
{
    /**
     * @param ServiceProviderInterface|string $provider
     * @param bool $force
     * @throws ExecutionException
     * @throws InvalidArgumentException
     * @throws ResourceDefinedException
     */
    public function registerProvider($provider, $force = false);

    /**
     * @param ServiceProviderInterface|string $provider
     * @throws InvalidArgumentException
     * @throws ResourceUndefinedException
     */
    public function unregisterProvider($provider);

    /**
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getProvider($provider);

    /**
     * @param string $providerClass
     * @return ServiceProviderInterface|null
     */
    public function resolveProviderClass($providerClass);
}
