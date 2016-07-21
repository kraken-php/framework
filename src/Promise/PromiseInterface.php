<?php

namespace Kraken\Promise;

use Error;
use Exception;

interface PromiseInterface extends DeferredInterface
{
    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null);

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @throws Error|Exception
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null);

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @return PromiseInterface
     */
    public function spread(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null);

    /**
     * @param callable $onSuccess
     * @return PromiseInterface
     */
    public function success(callable $onSuccess);

    /**
     * @param callable $onFailure
     * @return PromiseInterface
     */
    public function failure(callable $onFailure);

    /**
     * @param callable $onCancel
     * @return PromiseInterface
     */
    public function abort(callable $onCancel);

    /**
     * @param callable $onFulfilledOrRejected
     * @return PromiseInterface
     */
    public function always(callable $onFulfilledOrRejected);

    /**
     * @return bool
     */
    public function isPending();

    /**
     * @return bool
     */
    public function isFulfilled();
    /**
     * @return bool
     */
    public function isRejected();

    /**
     * @return bool
     */
    public function isCancelled();

    /**
     * @return mixed|null
     */
    public function value();

    /**
     * @return Error|Exception|string|null
     */
    public function reason();
}
