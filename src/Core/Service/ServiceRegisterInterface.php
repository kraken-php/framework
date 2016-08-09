<?php

namespace Kraken\Core\Service;

use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Logic\Resource\ResourceDefinedException;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;

interface ServiceRegisterInterface
{
    /**
     * Boot service register.
     *
     * On boot the following operations will be made:
     * 1. existing providers will be sorted to resolve its dependency trees.
     * 2. all providers will be registered meaning ::register() method will be fired on each of them.
     * 3. all providers will be booted meaning ::boot() method will be fired on each of them.
     * 4. existing aliases will be registered.
     *
     * ExecutionException is thrown if any of this steps fails.
     *
     * @throws ExecutionException
     */
    public function boot();

    /**
     * Register ServiceProvider.
     *
     * The $provider might be concrete object implementing ServiceProviderInterface or string representing the className.
     * In latter case, the object will be automatically created by ServiceRegister.
     *
     * If the provider is registered before boot its register method will be added to the queue of handlers to fire
     * on boot. On the other hand, if register is registered after boot, its register method will be fired immediately.
     *
     * ExecutionException is thrown if register handler of ServiceProvider has thrown any exception or error.
     * InvalidArgumentException is thrown if invalid Provider object or class was passed.
     * ResourceDefinedException is thrown if Provider of given class was already registered.
     *
     * @param ServiceProviderInterface|string $provider
     * @throws ExecutionException
     * @throws InvalidArgumentException
     * @throws ResourceDefinedException
     */
    public function registerProvider($provider);

    /**
     * Unregister previously registered ServiceProvider.
     *
     * The $provider might be concrete object implementing ServiceProviderInterface or string representing the className.
     * In both situations ServiceRegister will look up for already registered Provider of the same class and unregister
     * it.
     *
     * InvalidArgumentException is thrown if invalid Provider object or class was passed.
     * ResourceUndefinedException is thrown if Provider of given class was not found.
     *
     * @param ServiceProviderInterface|string $provider
     * @throws InvalidArgumentException
     * @throws ResourceUndefinedException
     */
    public function unregisterProvider($provider);

    /**
     * Get registered ServiceProvider.
     *
     * The $provider might be concrete object implementing ServiceProviderInterface or string representing the className.
     * In both situations ServiceRegister will look up for already registered Provider of the same class and return it.
     * If the object is not found, null will be returned.
     *
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getProvider($provider);

    /**
     * Gets list with classNames of all registered ServiceProvider.
     *
     * @return string[]
     */
    public function getProviders();

    /**
     * Gets list with classNames of all provided services.
     *
     * @return string[]
     */
    public function getServices();

    /**
     * Flush all registered providers.
     *
     * This method flushes all registered providers, if they were not booted. If the flush method is called after that
     * the IllegalCallException will be thrown.
     *
     * @throws IllegalCallException
     */
    public function flushProviders();

    /**
     * Register service alias.
     *
     * ExecutionException is thrown if Container::alias() method underneath throws any errors or exceptions.
     * ResourceDefinedException is thrown if alias is already registered.
     *
     * @param string $alias
     * @param string $existing
     * @throws ExecutionException
     * @throws ResourceDefinedException
     */
    public function registerAlias($alias, $existing);

    /**
     * Unregister previously registered service alias.
     *
     * ResourceUndefinedException is thrown if alias is not registered.
     *
     * @param string $alias
     * @throws ResourceUndefinedException
     */
    public function unregisterAlias($alias);

    /**
     * Return target of given alias. If the alias is not found null will be returned.
     *
     * @param string $alias
     * @return null|string
     */
    public function getAlias($alias);

    /**
     * Return list of all aliases and its targets in form of associative array.
     *
     * @return string[]
     */
    public function getAliases();

    /**
     * Flush all registered aliases.
     *
     * This method flushes all registered aliases, if register is not booted. If the flush method is called after that
     * the IllegalCallException will be thrown.
     *
     * @throws IllegalCallException
     */
    public function flushAliases();
}
