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
     * @resolves mixed
     * @rejects Error|Exception
     * @cancels Error|Exception
     */
    public function getPromise();

    /**
     * Resolve promise with specified value.
     *
     * @param mixed|null $value
     * @return PromiseInterface
     * @resolves mixed|null
     */
    public function resolve($value = null);

    /**
     * Reject promise with specified reason.
     *
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     * @rejects Error|Exception|string|null
     */
    public function reject($reason = null);

    /**
     * Cancel promise with specified reason.
     *
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     * @cancels Error|Exception|string|null
     */
    public function cancel($reason = null);
}
