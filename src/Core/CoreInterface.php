<?php

namespace Kraken\Core;

use Kraken\Container\ContainerInterface;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\InstantiationException;

interface CoreInterface extends ContainerInterface
{
    /**
     * Boot core and all of its registered providers and aliases.
     *
     * InstantiationException is thrown if any of providers throws error or exception during boot.
     *
     * @return CoreInterface
     * @throws InstantiationException
     */
    public function boot();

    /**
     * Add addition configuration to current default configuration.
     *
     * @param string[][]|null $config
     * @return string[][]
     */
    public function config($config = null);

    /**
     * Return current version of Framework.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Return Runtime type of container.
     *
     * Returned value might be one of: Runtime::UNIT_PROCESS, Runtime::UNIT_THREAD, Runtime::UNIT_UNDEFINED.
     *
     * @return string
     */
    public function getType();

    /**
     * Return directory path to project root.
     *
     * @return string
     */
    public function getBasePath();

    /**
     * Return directory path to framework data folder.
     *
     * @return string
     */
    public function getDataPath();

    /**
     * Return directory name of framework data folder.
     *
     * @return string
     */
    public function getDataDir();

    /**
     * Register collection of ServiceProviders.
     *
     * Collection of $providers might contain concrete objects or classNames for providers to create.
     *
     * ExecutionException is thrown if any of providers throws error or exception during registration.
     *
     * @param ServiceProviderInterface[]|string[] $providers
     * @throws ExecutionException
     */
    public function registerProviders($providers);

    /**
     * Register ServiceProvider.
     *
     * Argument for $provider might be concrete object or className for provider to create.
     *
     * ExecutionException is thrown if provider throws error or exception during registration.
     *
     * @param ServiceProviderInterface|string $provider
     * @throws ExecutionException
     */
    public function registerProvider($provider);

    /**
     * Unregister ServiceProvider.
     *
     * Argument for $provider might be concrete object or className for provider.
     *
     * ExecutionException is thrown if provider throws error or exception during unregistration.
     *
     * @param ServiceProviderInterface|string $provider
     * @throws ExecutionException
     */
    public function unregisterProvider($provider);

    /**
     * Return registered provider or null if not found.
     *
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getProvider($provider);

    /**
     * Return list of all registered providers' classNames.
     *
     * @return string[]
     */
    public function getProviders();

    /**
     * Return list of all registered providers' provided services.
     *
     * @return string[]
     */
    public function getServices();

    /**
     * Flush list of registered providers.
     *
     * This method flushes list of registered providers if it is invoked before boot, if it is called after that the
     * IllegalCallException is thrown.
     *
     * @throws IllegalCallException
     */
    public function flushProviders();

    /**
     * Register collection of service aliases.
     *
     * @param string[] $interfaces
     */
    public function registerAliases($interfaces);

    /**
     * Register alias to another service.
     *
     * ExecutionException is thrown if any error or exception is thrown in Container during registration.
     *
     * @param string $alias
     * @param string $interface
     * @throws ExecutionException
     */
    public function registerAlias($alias, $interface);

    /**
     * Unregister previously registered alias.
     *
     * ExecutionException is thrown if any error or exception is thrown in Container during unregistration.
     *
     * @param string $alias
     * @throws ExecutionException
     */
    public function unregisterAlias($alias);

    /**
     * Get service of passed alias or null if it does not exist.
     *
     * @param string $alias
     * @return string|null
     */
    public function getAlias($alias);

    /**
     * Get list of all registered aliases.
     *
     * @return string[]
     */
    public function getAliases();

    /**
     * Flush list of registered aliases.
     *
     * This method flushes list of registered aliases if it is invoked before boot, if it is called after that the
     * IllegalCallException is thrown.
     *
     * @throws IllegalCallException
     */
    public function flushAliases();
}
