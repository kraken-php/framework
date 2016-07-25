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
            return Promise::doResolve($onCancel($this->reason()))
                ->then(
                    function() {
                        return Promise::doCancel($this->reason());
                    },
                    function() {
                        return Promise::doCancel($this->reason());
                    }
                );
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        return Promise::doCancel($this->reason());
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
            $this->throwError($this->reason());
        }

        $result = $onCancel($this->reason());

        if ($result instanceof self)
        {
            $this->throwError($result->reason());
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
     * @return PromiseInterface
     */
    public function resolve($value = null)
    {
        return $this;
    }

    /**
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function reject($reason = null)
    {
        return $this;
    }

    /**
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function cancel($reason = null)
    {
        return $this;
    }

    /**
     * @return mixed|null
     */
    protected function value()
    {
        return null;
    }

    /**
     * @return Error|Exception|string|null
     */
    protected function reason()
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
