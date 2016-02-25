<?php

namespace Kraken\Core\Service;

use Kraken\Core\CoreInterface;
use Kraken\Throwable\Runtime\ExecutionException;

interface ServiceProviderInterface
{
    /**
     * @return string[]
     */
    public function requires();

    /**
     * @return string[]
     */
    public function provides();

    /**
     * @return bool
     */
    public function isRegistered();

    /**
     * @param CoreInterface $core
     * @throws ExecutionException
     */
    public function registerProvider(CoreInterface $core);

    /**
     * @param CoreInterface $core
     */
    public function unregisterProvider(CoreInterface $core);

    /**
     * @param CoreInterface $core
     * @throws ExecutionException
     */
    public function bootProvider(CoreInterface $core);
}
