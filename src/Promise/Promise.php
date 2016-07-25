<?php

namespace Kraken\Promise;

use Error;
use Exception;

class Promise implements PromiseInterface
{
    use PromiseStaticTrait;

    /**
     * @var PromiseInterface|null
     */
    protected $result;

    /**
     * @var callable[]
     */
    protected $handlers;

    /**
     * @var callable
     */
    protected $canceller;

    /**
     * @var int
     */
    protected $currentCancellations;

    /**
     * @var int
     */
    protected $requiredCancellations;

    /**
     * @param callable|null $resolver
     * @param callable|null $canceller
     */
    public function __construct(callable $resolver = null, callable $canceller = null)
    {
        $this->result = null;
        $this->handlers = [];
        $this->canceller = function($reason = null) use($canceller) {
            try
            {
                return $canceller !== null && ($result = $canceller($reason)) instanceof self ? $result : $this;
            }
            catch (Error $ex)
            {}
            catch (Exception $ex)
            {}
            return $this;
        };
        $this->currentCancellations = 0;
        $this->requiredCancellations = 0;

        $this->mutate($resolver);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->result);
        unset($this->handlers);
        unset($this->canceller);
        unset($this->currentCancellations);
        unset($this->requiredCancellations);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null !== $this->result)
        {
            return $this->getResult()->then($onFulfilled, $onRejected, $onCancel);
        }

        if (null !== $this->canceller)
        {
            $this->requiredCancellations++;

            $canceller = function($reason = null) {
                if (++$this->currentCancellations >= $this->requiredCancellations)
                {
                    return $this->cancel($reason);
                }
                return null;
            };
        }
        else
        {
            $canceller = null;
        }

        return new static(function($resolve, $reject, $cancel) use($onFulfilled, $onRejected, $onCancel) {

            $this->handlers[] = function(PromiseInterface $promise) use($resolve, $reject, $cancel, $onFulfilled, $onRejected, $onCancel) {
                $promise
                    ->then($onFulfilled, $onRejected, $onCancel)
                    ->done($resolve, $reject, $cancel)
                ;
            };
        }, $canceller);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null)
    {
        if (null !== $this->result)
        {
            $this->getResult()->done($onFulfilled, $onRejected, $onCancel);
        }

        $this->handlers[] = function(PromiseInterface $promise) use($onFulfilled, $onRejected, $onCancel) {
            $promise
                ->done($onFulfilled, $onRejected, $onCancel);
        };
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
            function($value) use($onFulfilledOrRejected) {
                return self::doResolve($onFulfilledOrRejected())->then(function() use($value) {
                    return $value;
                });
            },
            function($reason) use($onFulfilledOrRejected) {
                return self::doResolve($onFulfilledOrRejected())->then(function() use($reason) {
                    return new PromiseRejected($reason);
                });
            },
            function($reason) use($onFulfilledOrRejected) {
                return self::doResolve($onFulfilledOrRejected())->then(function() use($reason) {
                    return new PromiseCancelled($reason);
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
        return $this->result === null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isFulfilled()
    {
        return !$this->isPending() && $this->result->isFulfilled();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isRejected()
    {
        return !$this->isPending() && $this->result->isRejected();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isCancelled()
    {
        return !$this->isPending() && $this->result->isCancelled();
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
        if (null !== $this->result || $value === $this)
        {
            return $this->result;
        }

        return $this->settle(self::doResolve($value));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function reject($reason = null)
    {
        if (null !== $this->result || $reason === $this)
        {
            return $this->result;
        }

        return $this->settle(self::doReject($reason));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function cancel($reason = null)
    {
        if (null !== $this->result || $reason === $this)
        {
            return $this->result;
        }

        $target = $this;

        if (null !== $this->canceller)
        {
            $canceller = $this->canceller;
            $this->canceller = null;
            $target = $canceller($reason);
        }

        if ($target === $this)
        {
            return $target->settle(self::doCancel($reason));
        }

        return $target;
    }

    /**
     * Return primitive value associated with Promise.
     *
     * @return mixed|null
     */
    protected function getValue()
    {
        return $this->isFulfilled() ? $this->result->getValue() : null;
    }

    /**
     * Return rejection or cancellation reason for Promise.
     *
     * @return Error|Exception|string|null
     */
    protected function getReason()
    {
        return ($this->isRejected() || $this->isCancelled()) ? $this->result->getReason() : null;
    }

    /**
     * Settle Promise with another Promise.
     *
     * @see PromiseInterface::resolve
     * @see PromiseInterface::reject
     * @see PromiseInterface::cancel
     *
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    protected function settle(PromiseInterface $promise)
    {
        $handlers = $this->handlers;

        $this->result = $promise;
        $this->handlers = [];

        foreach ($handlers as $handler)
        {
            $handler($promise);
        }

        return $promise;
    }

    /**
     * Get Promise result. Returns fulfilled, rejected or cancelled Promise for settled Promises or null for pending.
     *
     * @return PromiseInterface|null
     */
    protected function getResult()
    {
        while ($this->result instanceof Promise && null !== $this->result->result)
        {
            $this->result = $this->result->result;
        }

        return $this->result;
    }

    /**
     * Mutate resolver.
     *
     * @param callable|null $resolver
     */
    protected function mutate(callable $resolver = null)
    {
        if ($resolver === null)
        {
            return;
        }

        try
        {
            $resolver(
                function ($value = null) {
                    $this->resolve($value);
                },
                function ($reason = null) {
                    $this->reject($reason);
                },
                function ($reason = null) {
                    $this->cancel($reason);
                }
            );
        }
        catch (Error $ex)
        {
            $this->reject($ex);
        }
        catch (Exception $ex)
        {
            $this->reject($ex);
        }
    }
}
