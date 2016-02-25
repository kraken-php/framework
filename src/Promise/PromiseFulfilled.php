<?php

namespace Kraken\Promise;

use Error;
use Exception;

class PromiseFulfilled implements PromiseInterface
{
    use PromiseStaticTrait;

    /**
     * @var mixed|null
     */
    protected $value;

    /**
     * @param mixed|null $value
     */
    public function __construct($value = null)
    {
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
     * @param callable|null $onProgress
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null, callable $onProgress = null)
    {
        if (null === $onFulfilled)
        {
            return $this;
        }

        try
        {
            return self::doResolve($onFulfilled($this->value));
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
     * @param callable|null $onProgress
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null, callable $onProgress = null)
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
     * @param callable|null $onProgress
     * @return PromiseInterface
     */
    public function spread(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null, callable $onProgress = null)
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
            },
            function($updates) use($onProgress) {
                call_user_func_array($onProgress, (array) $updates);
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
     * @param callable $onProgress
     * @return PromiseInterface
     */
    public function progress(callable $onProgress)
    {
        return $this->then(null, null, null, $onProgress);
    }

    /**
     * @param callable $onFulfilledOrRejected
     * @return PromiseInterface
     */
    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(
            function($value) use($onFulfilledOrRejected) {
                return self::doResolve($onFulfilledOrRejected())->then(function() use($value) {
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
     * @param mixed|null $update
     */
    public function notify($update = null)
    {
        // DoNothing
    }

    /**
     * @return mixed|null
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @return Error|Exception|string|null
     */
    public function reason()
    {
        return null;
    }
}
