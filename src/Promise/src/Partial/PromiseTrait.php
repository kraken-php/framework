<?php

namespace Kraken\Promise\Partial;

use Kraken\Promise\Helper\CancellationQueue;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseCancelled;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Promise\PromiseInterface;
use Kraken\Promise\PromiseRejected;
use Kraken\Throwable\Exception\Runtime\UnderflowException;

trait PromiseTrait
{
    /**
     * Resolve Promise or value.
     *
     * @param PromiseInterface|mixed $promiseOrValue
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public static function doResolve($promiseOrValue = null)
    {
        if (!$promiseOrValue instanceof PromiseInterface)
        {
            return new PromiseFulfilled($promiseOrValue);
        }

        return $promiseOrValue;
    }

    /**
     * Reject Promise or value.
     *
     * @param PromiseInterface|mixed $promiseOrValue
     * @return PromiseInterface
     * @rejects Error|Exception|string|null
     */
    public static function doReject($promiseOrValue = null)
    {
        if (!$promiseOrValue instanceof PromiseInterface)
        {
            return new PromiseRejected($promiseOrValue);
        }

        return self::doResolve($promiseOrValue)->then(function($value) {
            return new PromiseRejected($value);
        });
    }

    /**
     * Cancel Promise or value.
     *
     * @param PromiseInterface|mixed $promiseOrValue
     * @return PromiseInterface
     * @cancels Error|Exception|string|null
     */
    public static function doCancel($promiseOrValue = null)
    {
        if (!$promiseOrValue instanceof PromiseInterface)
        {
            return new PromiseCancelled($promiseOrValue);
        }

        return self::doResolve($promiseOrValue)
            ->then(
                function($value) {
                    return new PromiseCancelled($value);
                },
                function($value) {
                    return new PromiseCancelled($value);
                }
            );
    }

    /**
     * Return Promise that will resolve only once all the items in $promisesOrValues have resolved.
     *
     * Return Promise that will resolve only once all the items in $promisesOrValues have resolved. The resolution
     * value of the returned promise will be an array containing the resolution values of each of the items in
     * $promisesOrValues.
     *
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public static function all($promisesOrValues)
    {
        return self::map($promisesOrValues, function($val) {
            return $val;
        });
    }

    /**
     * Initiate a competitive race that allows one winner.
     *
     * Initiate a competitive race that allows one winner. Returns a promise which is resolved in the same way
     * the first settled promise resolves.
     *
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public static function race($promisesOrValues)
    {
        $cancellationQueue = new CancellationQueue();

        return new Promise(function($resolve, $reject, $cancel) use($promisesOrValues, $cancellationQueue) {
            self::doResolve($promisesOrValues)
                ->done(function($array) use($resolve, $reject, $cancel, $cancellationQueue) {
                    if (!is_array($array) || !$array)
                    {
                        $resolve();
                        return;
                    }

                    $fulfiller = function($value) use($resolve, $cancellationQueue) {
                        $resolve($value);
                        $cancellationQueue();
                    };

                    $rejecter = function($reason) use($reject, $cancellationQueue) {
                        $reject($reason);
                        $cancellationQueue();
                    };

                    foreach ($array as $promiseOrValue)
                    {
                        $cancellationQueue->enqueue($promiseOrValue);

                        self::doResolve($promiseOrValue)
                            ->done($fulfiller, $rejecter, $cancel);
                    }
                }, $reject, $cancel);
        }, $cancellationQueue);
    }

    /**
     * Return a promise that will resolve when any one of the items in $promisesOrValues resolves.
     *
     * Return a promise that will resolve when any one of the items in $promisesOrValues resolves. The resolution value
     * of the returned promise will be the resolution value of the triggering item.
     *
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public static function any($promisesOrValues)
    {
        return self::some($promisesOrValues, 1)
            ->then(function($val) {
                return array_shift($val);
            });
    }

    /**
     * Return Promise that will resolve when $howMany of the supplied items in $promisesOrValues resolve.
     *
     * Return Promise that will resolve when $howMany of the supplied items in $promisesOrValues resolve. The resolution
     * value of the returned promise will be an array of length $howMany containing the resolution values of
     * the triggering items.
     *
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @param int $howMany
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public static function some($promisesOrValues, $howMany)
    {
        $cancellationQueue = new CancellationQueue();

        return new Promise(function($resolve, $reject, $cancel) use($promisesOrValues, $howMany, $cancellationQueue) {
            self::doResolve($promisesOrValues)
                ->done(function($array) use($resolve, $reject, $cancel, $howMany, $cancellationQueue) {
                    if (!is_array($array) || $howMany < 1)
                    {
                        $resolve([]);
                        return;
                    }

                    $len = count($array);

                    if ($len < $howMany)
                    {
                        throw new UnderflowException(
                            sprintf('Input array must contain at least %d items but contains only %s items.', $howMany, $len)
                        );
                    }

                    $toResolve = $howMany;
                    $toReject  = ($len - $toResolve) + 1;
                    $values    = [];
                    $reasons   = [];

                    foreach ($array as $i=>$promiseOrValue)
                    {
                        $fulfiller = function($val) use($i, &$values, &$toResolve, $toReject, $resolve, $cancellationQueue) {
                            if ($toResolve < 1 || $toReject < 1)
                            {
                                return;
                            }

                            $values[$i] = $val;

                            if (0 === --$toResolve)
                            {
                                $resolve($values);
                                $cancellationQueue();
                            }
                        };

                        $rejecter = function($reason) use($i, &$reasons, &$toReject, $toResolve, $reject, $cancellationQueue) {
                            if ($toResolve < 1 || $toReject < 1)
                            {
                                return;
                            }

                            $reasons[$i] = $reason;

                            if (0 === --$toReject)
                            {
                                $reject($reasons);
                                $cancellationQueue();
                            }
                        };

                        $canceller = $cancel;

                        $cancellationQueue->enqueue($promiseOrValue);

                        self::doResolve($promiseOrValue)
                            ->done($fulfiller, $rejecter, $canceller);
                    }
                }, $reject, $cancel);
        }, $cancellationQueue);
    }

    /**
     * Map promises and/or values using specified $mapFunc.
     *
     * @see array_map
     *
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @param callable $mapFunc
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public static function map($promisesOrValues, callable $mapFunc)
    {
        $cancellationQueue = new CancellationQueue();

        return new Promise(function($resolve, $reject, $cancel) use($promisesOrValues, $mapFunc, $cancellationQueue) {
            self::doResolve($promisesOrValues)
                ->done(function($array) use($resolve, $reject, $cancel, $mapFunc, $cancellationQueue) {
                    if (!is_array($array) || !$array)
                    {
                        $resolve([]);
                        return;
                    }

                    $toResolve = count($array);
                    $values    = [];

                    foreach ($array as $i=>$promiseOrValue)
                    {
                        $cancellationQueue->enqueue($promiseOrValue);

                        self::doResolve($promiseOrValue)
                            ->then($mapFunc)
                            ->done(
                                function($mapped) use($i, &$values, &$toResolve, $resolve) {
                                    $values[$i] = $mapped;
                                    if (0 === --$toResolve)
                                    {
                                        $resolve($values);
                                    }
                                },
                                $reject,
                                $cancel
                            );
                    }
                }, $reject, $cancel);
        }, $cancellationQueue);
    }

    /**
     * Reduce Promises and/or values using $reduceFunc with $initialValue being Promise or primitive value.
     *
     * @see array_reduce
     *
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @param callable $reduceFunc
     * @param PromiseInterface|mixed|null $initialValue
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public static function reduce($promisesOrValues, callable $reduceFunc, $initialValue = null)
    {
        $cancellationQueue = new CancellationQueue();

        return new Promise(function($resolve, $reject, $cancel) use($promisesOrValues, $reduceFunc, $initialValue, $cancellationQueue) {
            self::doResolve($promisesOrValues)
                ->done(function($array) use($reduceFunc, $initialValue, $resolve, $reject, $cancel, $cancellationQueue) {
                    if (!is_array($array))
                    {
                        $array = [];
                    }

                    $total = count($array);
                    $i = 0;

                    // Wrap the supplied $reduceFunc with one that handles promises and then delegates to the supplied.
                    //
                    $wrappedReduceFunc = function(PromiseInterface $current, $val) use($reduceFunc, $cancellationQueue, $total, &$i) {
                        $cancellationQueue->enqueue($val);
                        return $current
                            ->then(function($c) use($reduceFunc, $total, &$i, $val) {
                                return self::doResolve($val)
                                    ->then(function($value) use($reduceFunc, $total, &$i, $c) {
                                        return $reduceFunc($c, $value, $i++, $total);
                                    });
                            });
                    };

                    $initialValue = self::doResolve($initialValue);

                    $cancellationQueue->enqueue($initialValue);

                    array_reduce($array, $wrappedReduceFunc, $initialValue)
                        ->done($resolve, $reject, $cancel);

                }, $reject, $cancel);
        }, $cancellationQueue);
    }
}
