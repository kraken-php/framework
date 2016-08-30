<?php

namespace Kraken\Core\Service;

use Kraken\Core\CoreInterface;
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
    public function registerProvider(CoreInterface $core)
    {
        try
        {
            $this->register($core);
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
    public function unregisterProvider(CoreInterface $core)
    {
        $this->unregister($core);
        $this->registered = false;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function bootProvider(CoreInterface $core)
    {
        try
        {
            $this->boot($core);
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
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function register(CoreInterface $core)
    {}

    /**
     * Unregister provider dependencies.
     *
     * This method should contain code to fire when Provider is being unregistered. All previously opened connectios,
     * streams, files and other vulnerable resources opened in ::register() should be closed here.
     *
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {}

    /**
     * Boot provider dependencies.
     *
     * This method should container code to fire when Provider is being booted. In comparison to ::register() method
     * this will be fired after all Providers have been already registered.
     *
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
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
