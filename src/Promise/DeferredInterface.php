<?php

namespace Kraken\Promise;

use Error;
use Exception;

interface DeferredInterface
{
    /**
     * @return PromiseInterface
     */
    public function promise();

    /**
     * @param mixed|null $value
     * @return PromiseInterface
     */
    public function resolve($value = null);

    /**
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function reject($reason = null);

    /**
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function cancel($reason = null);
}
