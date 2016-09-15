<?php

namespace Kraken\Core;

use Kraken\Container\Container;
use Kraken\Container\ServiceRegister;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Error;
use Exception;

class Core extends Container implements CoreInterface
{
    /**
     * @var string
     */
    const VERSION = '0.2.2';

    /**
     * @var string
     */
    const RUNTIME_UNIT = 'Undefined';

    /**
     * @var string
     */
    protected $dataPath;

    /**
     * @var string[][]
     */
    protected $bootConfig;

    /**
     * @var ServiceRegister
     */
    protected $serviceRegister;

    /**
     * @param string|null $dataPath
     * @throws InstantiationException
     */
    public function __construct($dataPath = null)
    {
        parent::__construct();

        $this->dataPath = realpath($dataPath);
        $this->bootConfig = [];
        $this->serviceRegister = new ServiceRegister($this);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->dataPath);
        unset($this->bootConfig);
        unset($this->serviceRegister);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function boot()
    {
        try
        {
            $this->bootProviders();

            return $this;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new InstantiationException("Core module could not be booted.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function config($config = null)
    {
        if ($config !== null)
        {
            $this->bootConfig = array_merge($this->bootConfig, $config);
        }

        return $this->bootConfig;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVersion()
    {
        return static::VERSION;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getType()
    {
        return static::RUNTIME_UNIT;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBasePath()
    {
        return dirname($this->dataPath);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getDataPath()
    {
        return $this->dataPath;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getDataDir()
    {
        return str_replace($this->getBasePath(), '', $this->getDataPath());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function registerProviders($providers)
    {
        foreach ($providers as $provider)
        {
            $this->registerProvider($provider);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function registerProvider($provider)
    {
        try
        {
            $this->serviceRegister->registerProvider($provider);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ExecutionException("Provider could not be registered.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unregisterProvider($provider)
    {
        try
        {
            $this->serviceRegister->unregisterProvider($provider);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ExecutionException("Provider could not be unregistered.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getProvider($provider)
    {
        return $this->serviceRegister->getProvider($provider);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getProviders()
    {
        return $this->serviceRegister->getProviders();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getServices()
    {
        return $this->serviceRegister->getServices();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function flushProviders()
    {
        $this->serviceRegister->flushProviders();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function registerAliases($interfaces)
    {
        foreach ($interfaces as $alias=>$interface)
        {
            $this->registerAlias($alias, $interface);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function registerAlias($alias, $interface)
    {
        try
        {
            $this->serviceRegister->registerAlias($alias, $interface);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ExecutionException("Alias could not be registered.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unregisterAlias($alias)
    {
        try
        {
            $this->serviceRegister->unregisterAlias($alias);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ExecutionException("Alias could not be unregistered.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAlias($alias)
    {
        return $this->serviceRegister->getAlias($alias);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAliases()
    {
        return $this->serviceRegister->getAliases();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function flushAliases()
    {
        $this->serviceRegister->flushAliases();
    }

    /**
     * Return list of default Providers.
     *
     * @return string[]
     */
    public function getDefaultProviders()
    {
        return [];
    }

    /**
     * Return list of default Aliases.
     *
     * @return string[]
     */
    public function getDefaultAliases()
    {
        return [];
    }

    /**
     * Boot providers.
     *
     * @throws ExecutionException
     */
    protected function bootProviders()
    {
        $this->serviceRegister->boot();
    }
}
