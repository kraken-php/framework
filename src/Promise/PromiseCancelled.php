<?php

namespace Kraken\Promise;

use Error;
use Exception;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Runtime\Execution\CancellationException;

class PromiseCancelled implements PromiseInterface
{
    /**
     * @var Error|Exception|string|null
     */
    protected $reason;

    /**
     * @param Error|Exception|string|null $reason
     * @throws InvalidArgumentException
     */
    public function __construct($reason = null)
    {
        if ($reason instanceof PromiseInterface)
        {
            throw new InvalidArgumentException(
                'You cannot create PromiseCancelled with a promise. Use Promise::doCancel($promiseOrValue) instead.'
            );
        }

        $this->reason = $reason;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->reason);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null === $onCancel)
        {
            return $this;
        }

        try
        {
            return Promise::doResolve($onCancel($this->getReason()))
                ->then(
                    function() {
                        return Promise::doCancel($this->getReason());
                    },
                    function() {
                        return Promise::doCancel($this->getReason());
                    }
                );
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        return Promise::doCancel($this->getReason());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null === $onCancel)
        {
            $this->throwError($this->getReason());
        }

        $result = $onCancel($this->getReason());

        if ($result instanceof self)
        {
            $this->throwError($result->getReason());
        }

        if ($result instanceof PromiseInterface)
        {
            $result->done();
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function spread(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        return $this->then(
            null,
            null,
            function($reasons) use($onCancel) {
                return call_user_func_array($onCancel, (array) $reasons);
            }
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function success(callable $onSuccess)
    {
        return $this->then($onSuccess);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function failure(callable $onFailure)
    {
        return $this->then(null, $onFailure);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function abort(callable $onCancel)
    {
        return $this->then(null, null, $onCancel);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(
            null,
            null,
            function($reason) use($onFulfilledOrRejected) {
                return Promise::doResolve($onFulfilledOrRejected())->then(function() use($reason) {
                    return new static($reason);
                });
            }
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPending()
    {
        return false;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isFulfilled()
    {
        return false;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isRejected()
    {
        return false;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isCancelled()
    {
        return true;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getPromise()
    {
        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resolve($value = null)
    {
        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function reject($reason = null)
    {
        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function cancel($reason = null)
    {
        return $this;
    }

    /**
     * @see Promise::getValue
     */
    protected function getValue()
    {
        return null;
    }

    /**
     * @see Promise::getReason
     */
    protected function getReason()
    {
        return $this->reason;
    }

    /**
     * @param Error|Exception|string $reason
     * @throws Error|Exception
     */
    protected function throwError($reason)
    {
        if ($reason instanceof Error || $reason instanceof Exception)
        {
            throw $reason;
        }

        throw new CancellationException($reason);
    }
}
