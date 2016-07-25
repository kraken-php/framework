<?php

namespace Kraken\Promise;

use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Error;
use Exception;

class PromiseFulfilled implements PromiseInterface
{
    /**
     * @var mixed|null
     */
    protected $value;

    /**
     * @param mixed|null $value
     * @throws InvalidArgumentException
     */
    public function __construct($value = null)
    {
        if ($value instanceof PromiseInterface)
        {
            throw new InvalidArgumentException(
                'You cannot create PromiseFulfilled with a promise. Use Promise::doResolve($promiseOrValue) instead.'
            );
        }

        $this->value = $value;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->value);
    }

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null === $onFulfilled)
        {
            return $this;
        }

        try
        {
            return Promise::doResolve($onFulfilled($this->value));
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
        if (null === $onFulfilled)
        {
            return;
        }

        $result = $onFulfilled($this->value);

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
            function($value) use($onFulfilledOrRejected) {
                return Promise::doResolve($onFulfilledOrRejected())->then(function() use($value) {
                    return $value;
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
        return true;
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
        return $this->value;
    }

    /**
     * @return Error|Exception|string|null
     */
    protected function reason()
    {
        return null;
    }
}
