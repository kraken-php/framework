<?php

namespace Kraken\Core\Service;

use Kraken\Core\CoreInterface;
use Kraken\Exception\Exception;
use Kraken\Exception\Runtime\ExecutionException;
use Kraken\Exception\Runtime\IllegalCallException;
use Kraken\Exception\Runtime\InvalidArgumentException;
use Kraken\Exception\Resource\ResourceDefinedException;
use Kraken\Exception\Resource\ResourceUndefinedException;
use Kraken\Exception\Runtime\OverflowException;

class ServiceRegister
{
    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var ServiceProviderInterface[]
     */
    protected $serviceProviders;

    /**
     * @var string[]
     */
    protected $serviceAliases;

    /**
     * @var bool
     */
    protected $booted;

    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
        $this->serviceProviders = [];
        $this->serviceAliases = [];
        $this->booted = false;
    }

    /**
     *
     */
    public function __destruct()
    {
        foreach ($this->serviceProviders as $provider)
        {
            try
            {
                $this->unregisterProvider($provider);
            }
            catch (Exception $ex)
            {}
        }

        unset($this->core);
        unset($this->serviceProviders);
        unset($this->serviceAliases);
        unset($this->booted);
    }

    /**
     * @throws ExecutionException
     */
    public function boot()
    {
        try
        {
            $this->sortProviders();
            $this->registerProviders();
            $this->bootProviders();
            $this->registerAliases();

            $this->booted = true;
        }
        catch (Exception $ex)
        {
            throw new ExecutionException("ServiceRegister could not be booted.", $ex);
        }
    }

    /**
     * @param ServiceProviderInterface|string $provider
     * @param bool $force
     * @throws ExecutionException
     * @throws InvalidArgumentException
     * @throws ResourceDefinedException
     */
    public function registerProvider($provider, $force = false)
    {
        if ($force === false && $this->getProvider($provider) !== null)
        {
            throw new ResourceDefinedException("ServiceProvider " . $this->getProviderClass($provider) . " already registered.");
        }

        if (is_string($provider) && ($class = $provider) && ($provider = $this->resolveProviderClass($provider)) === null)
        {
            throw new InvalidArgumentException("ServiceProvider $class is not valid provider.");
        }

        try
        {
            if ($this->booted)
            {
                $provider->registerProvider($this->core);
            }
        }
        catch (Exception $ex)
        {
            throw new ExecutionException("ServiceProvider " . $this->getProviderClass($provider) . " failed during registration.", $ex);
        }

        $this->markProviderRegistered($provider);
    }

    /**
     * @param ServiceProviderInterface|string $provider
     * @throws InvalidArgumentException
     * @throws ResourceUndefinedException
     */
    public function unregisterProvider($provider)
    {
        if (($registered = $this->getProvider($provider)) === null)
        {
            throw new ResourceUndefinedException("ServiceProvider " . $this->getProviderClass($provider) . " not registered.");
        }

        if (is_string($provider) && ($class = $provider) && ($provider = $this->resolveProviderClass($provider)) === null)
        {
            throw new InvalidArgumentException("ServiceProvider $class is not valid provider.");
        }

        $provider->unregisterProvider($this->core);

        $this->markProviderUnregistered($provider);
    }

    /**
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getProvider($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        foreach ($this->serviceProviders as $key=>$provider)
        {
            if ($provider instanceof $name)
            {
                return $provider;
            }
        }

        return null;
    }

    /**
     * @param string $providerClass
     * @return ServiceProviderInterface|null
     */
    public function resolveProviderClass($providerClass)
    {
        if (!class_exists($providerClass) || ($provider = new $providerClass($this)) instanceof ServiceProviderInterface === false)
        {
            return null;
        }

        return $provider;
    }

    /**
     * @return string[]
     */
    public function getProviders()
    {
        $names = [];
        foreach ($this->serviceProviders as $provider)
        {
            $names[] = get_class($provider);
        }

        return $names;
    }

    /**
     * @return string[]
     */
    public function getServices()
    {
        $services = [];
        foreach ($this->serviceProviders as $provider)
        {
            $services = array_merge($services, $provider->provides());
        }

        return $services;
    }

    /**
     * @throws IllegalCallException
     */
    public function flushProviders()
    {
        if ($this->booted)
        {
            throw new IllegalCallException("\$this->flushProviders() cannot be called after boot up.");
        }

        $this->serviceProviders = [];
    }

    /**
     * @param string $alias
     * @param string $concrete
     * @throws ExecutionException
     */
    public function registerAlias($alias, $concrete)
    {
        $this->serviceAliases[$alias] = $concrete;

        if ($this->booted)
        {
            try
            {
                $this->core->alias($alias, $concrete);
            }
            catch (Exception $ex)
            {
                throw new ExecutionException("Alias [$alias] could not be registered.", $ex);
            }
        }
    }

    /**
     * @param string $alias
     */
    public function unregisterAlias($alias)
    {
        unset($this->serviceAliases[$alias]);
    }

    /**
     * @param string $alias
     * @return null|string
     */
    public function getAlias($alias)
    {
        return isset($this->serviceAliases[$alias]) ? $this->serviceAliases[$alias] : null;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return $this->serviceAliases;
    }

    /**
     * @throws IllegalCallException
     */
    public function flushAliases()
    {
        if ($this->booted)
        {
            throw new IllegalCallException("\$this->flushAliases() cannot be called after boot up.");
        }

        $this->serviceAliases = [];
    }

    /**
     * @param ServiceProviderInterface $provider
     */
    protected function markProviderRegistered(ServiceProviderInterface $provider)
    {
        $this->serviceProviders[] = $provider;
    }

    /**
     * @param ServiceProviderInterface $provider
     */
    protected function markProviderUnregistered(ServiceProviderInterface $provider)
    {
        foreach ($this->serviceProviders as $index=>$currentProvider)
        {
            if ($provider instanceof $currentProvider)
            {
                unset($this->serviceProviders[$index]);
                break;
            }
        }
    }

    /**
     * @throws OverflowException
     */
    private function sortProviders()
    {
        $this->serviceProviders = (new ServiceSorter)->sortProviders($this->serviceProviders);
    }

    /**
     * @throws ExecutionException
     */
    private function registerProviders()
    {
        foreach ($this->serviceProviders as $provider)
        {
            try
            {
                if (!$provider->isRegistered())
                {
                    $provider->registerProvider($this->core);
                }
            }
            catch (Exception $ex)
            {
                throw new ExecutionException("ServiceProvider " . $this->getProviderClass($provider) . " failed during registration.", $ex);
            }
        }
    }

    /**
     * @throws ExecutionException
     */
    private function registerAliases()
    {
        foreach ($this->serviceAliases as $alias=>$concrete)
        {
            try
            {
                $this->core->alias($alias, $concrete);
            }
            catch (Exception $ex)
            {
                throw new ExecutionException("Alias [$alias] could not have be registered.", $ex);
            }
        }
    }

    /**
     * @throws ExecutionException
     */
    private function bootProviders()
    {
        foreach ($this->serviceProviders as $provider)
        {
            try
            {
                $provider->bootProvider($this->core);
            }
            catch (Exception $ex)
            {
                throw new ExecutionException("ServiceProvider " . $this->getProviderClass($provider) . " failed during boot.", $ex);
            }
        }
    }

    /**
     * @param ServiceProviderInterface|string $provider
     * @return string
     */
    private function getProviderClass($provider)
    {
        return is_string($provider) ? $provider : get_class($provider);
    }
}
