<?php

namespace Kraken\Promise;

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
     * @var callable[]
     */
    protected $progressHandlers;

    /**
     * @var callable[]
     */
    protected $cancellers;

    /**
     * @param callable|null $resolver
     */
    public function __construct(callable $resolver = null)
    {
        $this->result = null;
        $this->handlers = [];
        $this->progressHandlers = [];
        $this->cancellers = [];

        $this->mutate($resolver);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->result);
        unset($this->handlers);
        unset($this->progressHandlers);
        unset($this->cancellers);
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
        if (null !== $this->result)
        {
            return $this->result()->then($onFulfilled, $onRejected, $onCancel, $onProgress);
        }

        return new static(function($resolve, $reject, $cancel, $notify) use($onFulfilled, $onRejected, $onCancel, $onProgress) {
            if ($onProgress)
            {
                $progressHandler = function($update) use($notify, $onProgress) {
                    try
                    {
                        $notify($onProgress($update));
                    }
                    catch (Exception $ex)
                    {
                        $notify($ex);
                    }
                };
            }
            else
            {
                $progressHandler = $notify;
            }

            $this->handlers[] = function(PromiseInterface $promise) use($resolve, $reject, $cancel, $onFulfilled, $onRejected, $onCancel, $progressHandler) {
                $promise
                    ->then($onFulfilled, $onRejected, $onCancel)
                    ->done($resolve, $reject, $cancel, $progressHandler)
                ;
            };

            $this->progressHandlers[] = $progressHandler;

            $this->cancellers[] = $cancel;
        });
    }

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onCancel
     * @param callable|null $onProgress
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onCancel = null, callable $onProgress = null)
    {
        if (null !== $this->result)
        {
            $this->result()->done($onFulfilled, $onRejected, $onCancel, $onProgress);
        }

        $this->handlers[] = function (PromiseInterface $promise) use ($onFulfilled, $onRejected, $onCancel) {
            $promise
                ->done($onFulfilled, $onRejected, $onCancel);
        };

        if ($onProgress !== null)
        {
            $this->progressHandlers[] = $onProgress;
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
     * @return bool
     */
    public function isPending()
    {
        return $this->result === null;
    }

    /**
     * @return bool
     */
    public function isFulfilled()
    {
        return !$this->isPending() && $this->result->isFulfilled();
    }

    /**
     * @return bool
     */
    public function isRejected()
    {
        return !$this->isPending() && $this->result->isRejected();
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return !$this->isPending() && $this->result->isCancelled();
    }

    /**
     * @param mixed $value
     * @return PromiseInterface
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
     * @param Exception|string $reason
     * @return PromiseInterface
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
     * @param Exception|string $reason
     * @return PromiseInterface
     */
    public function cancel($reason = null)
    {
        if ($reason === $this)
        {
            return $this->result;
        }
        else if (null !== $this->result)
        {
            foreach ($this->cancellers as $canceller)
            {
                $canceller($reason);
            }

            return $this->result;
        }

        return $this->settle(self::doCancel($reason));
    }

    /**
     * @param mixed $update
     * @return PromiseInterface
     */
    public function notify($update = null)
    {
        if (null !== $this->result || $update === $this)
        {
            return $this->result;
        }

        foreach ($this->progressHandlers as $handler)
        {
            $handler($update);
        }

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function value()
    {
        return $this->isFulfilled() ? $this->result->value() : null;
    }

    /**
     * @return Exception|string|null
     */
    public function reason()
    {
        return ($this->isRejected() || $this->isCancelled()) ? $this->result->reason() : null;
    }

    /**
     * @param PromiseInterface $promise
     * @return PromiseInterface
     */
    protected function settle(PromiseInterface $promise)
    {
        $handlers = $this->handlers;

        $this->result = $promise;
        $this->handlers = [];
        $this->progressHandlers = [];

        foreach ($handlers as $handler)
        {
            $handler($promise);
        }

        return $promise;
    }

    /**
     * @return PromiseInterface|null
     */
    protected function result()
    {
        while ($this->result instanceof Promise && null !== $this->result->result)
        {
            $this->result = $this->result->result;
        }

        return $this->result;
    }

    /**
     * @param callable|null $resolver
     */
    protected function mutate(callable $resolver = null)
    {
        if ($resolver === null)
        {
            return;
        }

        $resolver(
            function($value = null) {
                $this->resolve($value);
            },
            function($reason = null) {
                $this->reject($reason);
            },
            function($reason = null) {
                $this->cancel($reason);
            },
            function($update = null) {
                $this->notify($update);
            }
        );
    }
}
