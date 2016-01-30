<?php

namespace Kraken\Core;

use Kraken\Container\ContainerInterface;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Exception\Runtime\ExecutionException;
use Kraken\Exception\Runtime\IllegalCallException;
use Kraken\Exception\Runtime\InstantiationException;

interface CoreInterface extends ContainerInterface
{
    /**
     * @return CoreInterface
     * @throws InstantiationException
     */
    public function boot();

    /**
     * @param string[][]|null $config
     * @return string[][]
     */
    public function config($config = null);

    /**
     * @return string
     */
    public function version();

    /**
     * @return string
     */
    public function unit();

    /**
     * @return string
     */
    public function basePath();

    /**
     * @return string
     */
    public function dataPath();

    /**
     * @return string
     */
    public function dataDir();

    /**
     * @param ServiceProviderInterface[]|string[] $providers
     * @param bool $force
     * @throws ExecutionException
     */
    public function registerProviders($providers, $force = false);

    /**
     * @param ServiceProviderInterface|string $provider
     * @param bool $force
     * @throws ExecutionException
     */
    public function registerProvider($provider, $force = false);

    /**
     * @param ServiceProviderInterface|string $provider
     * @throws ExecutionException
     */
    public function unregisterProvider($provider);

    /**
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getProvider($provider);

    /**
     * @return string[]
     */
    public function getProviders();

    /**
     * @return string[]
     */
    public function getServices();

    /**
     * @throws IllegalCallException
     */
    public function flushProviders();

    /**
     * @param string[] $interfaces
     */
    public function registerAliases($interfaces);

    /**
     * @param string $alias
     * @param string $interface
     */
    public function registerAlias($alias, $interface);

    /**
     * @param string $alias
     */
    public function unregisterAlias($alias);

    /**
     * @param string $alias
     * @return string
     */
    public function getAlias($alias);

    /**
     * @return string[]
     */
    public function getAliases();

    /**
     * @throws IllegalCallException
     */
    public function flushAliases();
}
