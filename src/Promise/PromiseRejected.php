<?php

namespace Kraken\Promise;

use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Throwable\LazyException;
use Error;
use Exception;

class PromiseRejected implements PromiseInterface
{
    /**
     * @var Error|Exception|LazyException|string|null
     */
    protected $reason;

    /**
     * @param Error|Exception|string|null
     * @throws InvalidArgumentException
     */
    public function __construct($reason = null)
    {
        if ($reason instanceof PromiseInterface)
        {
            throw new InvalidArgumentException(
                'You cannot create PromiseRejected with a promise. Use Promise::doReject($promiseOrValue) instead.'
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
        if (null === $onRejected)
        {
            return $this;
        }

        try
        {
            return Promise::doResolve($onRejected($this->getReason()));
        }
        catch (Error $ex)
        {
            return new PromiseRejected($ex);
        }
        catch (Exception $ex)
        {
            return new PromiseRejected($ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null === $onRejected)
        {
            $this->throwError($this->getReason());
        }

        $result = $onRejected($this->getReason());

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
            function($values) use($onFulfilled) {
                call_user_func_array($onFulfilled, (array) $values);
            },
            function($rejections) use($onRejected) {
                call_user_func_array($onRejected, (array) $rejections);
            },
            function($reasons) use($onCancel) {
                call_user_func_array($onCancel, (array) $reasons);
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
        return true;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isCancelled()
    {
        return false;
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
        return ($this->reason instanceof LazyException) ? $this->reason->toException() : $this->reason;
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

        throw new RejectionException($reason);
    }
}
