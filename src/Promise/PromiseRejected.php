<?php

namespace Kraken\Promise;

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
        if (null === $onRejected)
        {
            return $this;
        }

        try
        {
            return Promise::doResolve($onRejected($this->reason()));
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
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null === $onRejected)
        {
            $this->throwError($this->reason());
        }

        $result = $onRejected($this->reason());

        if ($result instanceof self)
        {
            $this->throwError($this->reason());
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
        return true;
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return false;
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
        return ($this->reason instanceof LazyException) ? $this->reason->toException() : $this->reason;
    }

    /**
     * @param Error|Exception|string $reason
     * @throws Error|Exception
     */
    protected function throwError($reason)
    {
        if ($reason instanceof Exception)
        {
            throw $reason;
        }

        throw new RejectionException($reason);
    }
}
