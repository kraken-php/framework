<?php

namespace Kraken\Container\Service;

use Kraken\Container\ServiceProviderInterface;
use Dazzle\Throwable\Exception\Logic\ResourceUndefinedException;
use Dazzle\Throwable\Exception\Runtime\OverflowException;

class ServiceSorter
{
    /**
     * @var string[][]
     */
    protected $reqs = [];

    /**
     * @var string[][]
     */
    protected $pvds = [];

    /**
     * @var string[]
     */
    protected $obtainableIn = [];

    /**
     * @var bool[]
     */
    protected $ordered = [];

    /**
     * @var ServiceProviderInterface[]
     */
    protected $instances = [];

    /**
     *
     */
    public function __destruct()
    {
        unset($this->reqs);
        unset($this->pvds);
        unset($this->obtainableIn);
        unset($this->ordered);
        unset($this->instances);
    }

    /**
     * Analyze required and provided dependencies of providers and sort the list to ensure right order while resolving
     * dependency tree.
     *
     * @param ServiceProviderInterface[] $providers
     * @return ServiceProviderInterface[]
     * @throws OverflowException
     * @throws ResourceUndefinedException
     */
    public function sortProviders($providers)
    {
        foreach ($providers as $provider)
        {
            $providerName = get_class($provider);
            $this->reqs[$providerName] = $provider->getRequires();
            $this->pvds[$providerName] = $provider->getProvides();
            $this->instances[$providerName] = $provider;

            foreach ($provider->getProvides() as $resource)
            {
                $this->obtainableIn[$resource] = $providerName;
            }
        }

        $newOrder = [];

        foreach ($providers as $provider)
        {
            $orderedProviders = $this->orderProvider($provider);

            foreach ($orderedProviders as $orderedProvider)
            {
                $newOrder[] = $this->instances[$orderedProvider];
            }
        }

        return $newOrder;
    }

    /**
     * @param ServiceProviderInterface $provider
     * @return string[]
     * @throws OverflowException
     * @throws ResourceUndefinedException
     */
    private function orderProvider(ServiceProviderInterface $provider)
    {
        $localOrder = [];
        $requires = $provider->getRequires();
        $provides = $provider->getProvides();

        if (!empty($provides))
        {
            foreach ($provides as $resource)
            {
                $localOrder = array_merge(
                    $localOrder,
                    $this->orderResource($resource)
                );
            }
        }
        else
        {
            foreach ($requires as $resource)
            {
                $localOrder = array_merge(
                    $localOrder,
                    $this->orderResource($resource)
                );
            }

            $localOrder[] = get_class($provider);
        }

        return $localOrder;
    }

    /**
     * @param string $resourceName
     * @param string[] &$localStack
     * @return string[]
     * @throws ResourceUndefinedException
     * @throws OverflowException
     */
    private function orderResource($resourceName, &$localStack = [])
    {
        $providerName = isset($this->obtainableIn[$resourceName]) ? $this->obtainableIn[$resourceName] : null;

        if ($providerName === null)
        {
            throw new ResourceUndefinedException("One of service providers could not be ordered because of undefined resource [$resourceName].");
        }

        if (isset($this->ordered[$resourceName]))
        {
            return [];
        }

        if (isset($localStack[$resourceName]))
        {
            throw new OverflowException("Service $providerName could not be ordered because of circular dependency to resource [$resourceName].");
        }

        $reqs = $this->reqs[$providerName];
        $pvds = $this->pvds[$providerName];

        $localOrder = [];
        $localStack[$resourceName] = true;

        foreach ($reqs as $resourceReq)
        {
            $localOrder = array_merge(
                $localOrder,
                $this->orderResource($resourceReq, $localStack)
            );
        }

        foreach ($pvds as $resourcePvd)
        {
            $this->ordered[$resourcePvd] = true;
        }

        $localOrder[] = $providerName;

        return $localOrder;
    }
}
