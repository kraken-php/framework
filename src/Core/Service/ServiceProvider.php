<?php

namespace Kraken\Core\Service;

use Kraken\Container\ContainerInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var bool
     */
    protected $registered = false;

    /**
     * @var bool
     */
    protected $booted = false;

    /**
     * @var string[]
     */
    protected $requires = [];

    /**
     * @var string[]
     */
    protected $provides = [];

    /**
     * @override
     * @inheritDoc
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getProvides()
    {
        return $this->provides;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isRegistered()
    {
        return $this->registered;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function registerProvider(ContainerInterface $container)
    {
        try
        {
            $this->register($container);
            $this->registered = true;
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        $this->throwException($ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unregisterProvider(ContainerInterface $container)
    {
        $this->unregister($container);
        $this->registered = false;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function bootProvider(ContainerInterface $container)
    {
        try
        {
            $this->boot($container);
            $this->booted = true;
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        $this->throwException($ex);
    }

    /**
     * Register provider dependencies.
     *
     * This method should contain code to fire when Provider is being registered.
     *
     * @param ContainerInterface $container
     * @throws Exception
     */
    protected function register(ContainerInterface $container)
    {}

    /**
     * Unregister provider dependencies.
     *
     * This method should contain code to fire when Provider is being unregistered. All previously opened connectios,
     * streams, files and other vulnerable resources opened in ::register() should be closed here.
     *
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {}

    /**
     * Boot provider dependencies.
     *
     * This method should container code to fire when Provider is being booted. In comparison to ::register() method
     * this will be fired after all Providers have been already registered.
     *
     * @param ContainerInterface $container
     * @throws Exception
     */
    protected function boot(ContainerInterface $container)
    {}

    /**
     * @param Error|Exception $ex
     * @throws ExecutionException
     */
    private function throwException($ex)
    {
        throw new ExecutionException("ServiceProvider [" . get_class($this) . "] raised an error.", $ex);
    }
}
