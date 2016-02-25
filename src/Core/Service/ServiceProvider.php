<?php

namespace Kraken\Core\Service;

use Error;
use Exception;
use Kraken\Core\CoreInterface;
use Kraken\Exception\Runtime\ExecutionException;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var bool
     */
    protected $registered = false;

    /**
     * @var string[]
     */
    protected $requires = [];

    /**
     * @var string[]
     */
    protected $provides = [];

    /**
     * @return string[]
     */
    public function requires()
    {
        return $this->requires;
    }

    /**
     * @return string[]
     */
    public function provides()
    {
        return $this->provides;
    }

    /**
     * @return bool
     */
    public function isRegistered()
    {
        return $this->registered;
    }

    /**
     * @param CoreInterface $core
     * @throws ExecutionException
     */
    public function registerProvider(CoreInterface $core)
    {
        try
        {
            $this->register($core);
            $this->registered = true;
        }
        catch (Error $ex)
        {
            $this->exception($ex);
        }
        catch (Exception $ex)
        {
            $this->exception($ex);
        }
    }

    /**
     * @param CoreInterface $core
     */
    public function unregisterProvider(CoreInterface $core)
    {
        $this->unregister($core);
        $this->registered = false;
    }

    /**
     * @param CoreInterface $core
     * @throws ExecutionException
     */
    public function bootProvider(CoreInterface $core)
    {
        try
        {
            $this->boot($core);
        }
        catch (Error $ex)
        {
            $this->exception($ex);
        }
        catch (Exception $ex)
        {
            $this->exception($ex);
        }
    }

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function register(CoreInterface $core)
    {}

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {}

    /**
     * @param CoreInterface $core
     * @throws Exception
     */
    protected function boot(CoreInterface $core)
    {}

    /**
     * @param Error|Exception $ex
     * @throws ExecutionException
     */
    protected function exception($ex)
    {
        throw new ExecutionException("ServiceProvider [" . get_class($this) . "] raised an error.", $ex);
    }
}
