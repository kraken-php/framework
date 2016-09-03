<?php

namespace Kraken\Core\Service;

use Kraken\Container\ContainerInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;

interface ServiceProviderInterface
{
    /**
     * Return list of Provider's required dependencies.
     *
     * @return string[]
     */
    public function getRequires();

    /**
     * Return list of Provider's provided dependencies.
     *
     * @return string[]
     */
    public function getProvides();

    /**
     * Return bool value representing whether Provider was already registered.
     *
     * @return bool
     */
    public function isRegistered();

    /**
     * Return bool value representing whether Provider was already booted.
     *
     * @return bool
     */
    public function isBooted();

    /**
     * Register Provider.
     *
     * This method registers Provider and calls its ::register() method.
     *
     * @param ContainerInterface $container
     * @throws ExecutionException
     */
    public function registerProvider(ContainerInterface $container);

    /**
     * Unregister Provider
     *
     * This method unregisters Provider and calls its ::unregister() method.
     *
     * @param ContainerInterface $container
     */
    public function unregisterProvider(ContainerInterface $container);

    /**
     * Boot Provider.
     *
     * This method boots Provider and calls its ::boot() method.
     *
     * @param ContainerInterface $container
     * @throws ExecutionException
     */
    public function bootProvider(ContainerInterface $container);
}
