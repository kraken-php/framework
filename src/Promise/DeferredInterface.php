<?php

namespace Kraken\Promise;

use Error;
use Exception;

interface DeferredInterface
{
    /**
     * Return promise representing return value of deferred operation.
     *
     * @return PromiseInterface
     */
    public function getPromise();

    /**
     * Resolve promise with specified value.
     *
     * @param mixed|null $value
     * @return PromiseInterface
     */
    public function resolve($value = null);

    /**
     * Reject promise with specified reason.
     *
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function reject($reason = null);

    /**
     * Cancel promise with specified reason.
     *
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function cancel($reason = null);
}
