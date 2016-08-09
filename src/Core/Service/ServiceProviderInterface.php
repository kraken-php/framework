<?php

namespace Kraken\Core\Service;

use Kraken\Core\CoreInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;

interface ServiceProviderInterface
{
    /**
     * Return list of Provider's required dependencies.
     *
     * @return string[]
     */
    public function requires();

    /**
     * Return list of Provider's provided dependencies.
     *
     * @return string[]
     */
    public function provides();

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
     * @param CoreInterface $core
     * @throws ExecutionException
     */
    public function registerProvider(CoreInterface $core);

    /**
     * Unregister Provider
     *
     * This method unregisters Provider and calls its ::unregister() method.
     *
     * @param CoreInterface $core
     */
    public function unregisterProvider(CoreInterface $core);

    /**
     * Boot Provider.
     *
     * This method boots Provider and calls its ::boot() method.
     *
     * @param CoreInterface $core
     * @throws ExecutionException
     */
    public function bootProvider(CoreInterface $core);
}
