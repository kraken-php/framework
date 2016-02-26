<?php

namespace Kraken\Core\Service;

use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Logic\Resource\ResourceDefinedException;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;

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
