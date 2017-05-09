<?php

namespace Kraken\Promise;

use Error;
use Exception;

interface PromiseInterface extends DeferredInterface
{
    /**
     * Transform Promise's value by applying a function to the Promise's fulfillment, rejection or cancellation value.
     * Returns a new promise for the transformed result.
     *
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null);

    /**
     * Consume the Promise's ultimate value if the promise fulfills or handle the ultimate error and cancellation.
     * Returns null.
     *
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @return null
     * @throws Error|Exception
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null);

    /**
     * Apply then transformation callbacks that automatically spreads received array into separate arguments.
     * Returns a new promise for the transformed result.
     *
     * @see PromiseInterface::then
     *
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function spread(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null);

    /**
     * Transform Promise's value by applying a function to the Promise's fulfillment value. Returns a new promise
     * for the transformed result.
     *
     * @see PromiseInterface::then
     *
     * @param callable $onSuccess
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function success(callable $onSuccess);

    /**
     * Transform Promise's value by applying a function to the Promise's rejection value. Returns a new promise
     * for the transformed result.
     *
     * @see PromiseInterface::then
     *
     * @param callable $onFailure
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function failure(callable $onFailure);

    /**
     * Transform Promise's value by applying a function to the Promise's cancellation value. Returns a new promise
     * for the transformed result.
     *
     * @see PromiseInterface::then
     *
     * @param callable $onCancel
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function abort(callable $onCancel);

    /**
     * Apply cleanup handler that fires regardless of Promise resolution state and suppress return value.
     *
     * @see PromiseInterface::then
     *
     * @param callable $onFulfilledOrRejected
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function always(callable $onFulfilledOrRejected);

    /**
     * Check if Promise is still pending.
     *
     * @return bool
     */
    public function isPending();

    /**
     * Check if Promise is fulfilled.
     *
     * @return bool
     */
    public function isFulfilled();

    /**
     * Check if Promise is rejected.
     *
     * @return bool
     */
    public function isRejected();

    /**
     * Check if Promise is cancelled.
     *
     * @return bool
     */
    public function isCancelled();
}
