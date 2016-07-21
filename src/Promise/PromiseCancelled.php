<?php

namespace Kraken\Promise;

use Error;
use Exception;

class PromiseCancelled implements PromiseInterface
{
    /**
     * @var Error|Exception|string|null
     */
    protected $reason;

    /**
     * @param Error|Exception|string|null $reason
     */
    public function __construct($reason = null)
    {
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
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null === $onCancel)
        {
            return $this;
        }

        try
        {
            return Promise::doCancel($onCancel($this->reason));
        }
        catch (Error $ex)
        {
            return new PromiseCancelled($ex);
        }
        catch (Exception $ex)
        {
            return new PromiseCancelled($ex);
        }
    }

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @throws Error|Exception
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null === $onCancel)
        {
            $this->throwError($this->reason);
        }

        $result = $onCancel($this->reason);

        if ($result instanceof self)
        {
            $this->throwError($this->reason);
        }

        if ($result instanceof PromiseInterface)
        {
            $result->done();
        }
    }

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @return PromiseInterface
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
     * @param callable $onSuccess
     * @return PromiseInterface
     */
    public function success(callable $onSuccess)
    {
        return $this->then($onSuccess);
    }

    /**
     * @param callable $onFailure
     * @return PromiseInterface
     */
    public function failure(callable $onFailure)
    {
        return $this->then(null, $onFailure);
    }

    /**
     * @param callable $onCancel
     * @return PromiseInterface
     */
    public function abort(callable $onCancel)
    {
        return $this->then(null, null, $onCancel);
    }

    /**
     * @param callable $onFulfilledOrRejected
     * @return PromiseInterface
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
     * @return bool
     */
    public function isPending()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isFulfilled()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isRejected()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return true;
    }

    /**
     * @return PromiseInterface
     */
    public function promise()
    {
        return $this;
    }

    /**
     * @param mixed|null $value
     */
    public function resolve($value = null)
    {
        // DoNothing
    }

    /**
     * @param Error|Exception|string|null $reason
     */
    public function reject($reason = null)
    {
        // DoNothing
    }

    /**
     * @param Error|Exception|string|null $reason
     */
    public function cancel($reason = null)
    {
        // DoNothing
    }

    /**
     * @return mixed|null
     */
    public function value()
    {
        return null;
    }

    /**
     * @return Error|Exception|string|null
     */
    public function reason()
    {
        return $this->reason;
    }

    /**
     * @param Error|Exception|string $reason
     * @throws Error|Exception
     */
    protected function throwError($reason)
    {
        if ($reason instanceof Exception || $reason instanceof Error)
        {
            throw $reason;
        }

        throw new Exception($reason);
    }
}
