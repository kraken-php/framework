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
     * @override
     * @inheritDoc
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null === $onFulfilled)
        {
            return $this;
        }

        try
        {
            return Promise::doResolve($onFulfilled($this->getValue()));
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
        if (null === $onFulfilled)
        {
            return;
        }

        $result = $onFulfilled($this->getValue());

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
                return call_user_func_array($onFulfilled, (array) $values);
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
            function($value) use($onFulfilledOrRejected) {
                return Promise::doResolve($onFulfilledOrRejected())->then(function() use($value) {
                    return $value;
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
        return true;
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
        return $this->value;
    }

    /**
     * @see Promise::getReason
     */
    protected function getReason()
    {
        return null;
    }
}
