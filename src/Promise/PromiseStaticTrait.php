<?php

namespace Kraken\Promise;

trait PromiseStaticTrait
{
    /**
     * @param PromiseInterface|mixed $promiseOrValue
     * @return PromiseInterface
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
     * @param PromiseInterface|mixed $promiseOrValue
     * @return PromiseInterface
     */
    public static function doReject($promiseOrValue = null)
    {
        if (!$promiseOrValue instanceof PromiseInterface)
        {
            return new PromiseRejected($promiseOrValue);
        }

        return $promiseOrValue;
    }

    /**
     * @param PromiseInterface|mixed $promiseOrValue
     * @return PromiseInterface
     */
    public static function doCancel($promiseOrValue = null)
    {
        if (!$promiseOrValue instanceof PromiseInterface)
        {
            return new PromiseCancelled($promiseOrValue);
        }

        return $promiseOrValue;
    }

    /**
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @return PromiseInterface
     */
    public static function all($promisesOrValues)
    {
        return self::doResolve($promisesOrValues)
            ->then(function($array) {
                if (!is_array($array) || !$array)
                {
                    return self::doResolve([]);
                }

                return new Promise(function($resolve, $reject, $cancel, $notify) use($array) {
                    $toResolve = count($array);
                    $values    = [];

                    foreach ($array as $i=>$promiseOrValue)
                    {
                        self::doResolve($promiseOrValue)
                            ->done(
                                function($value) use($i, &$values, &$toResolve, $resolve) {
                                    $values[$i] = $value;

                                    if (0 === --$toResolve)
                                    {
                                        $resolve($values);
                                    }
                                },
                                $reject,
                                $cancel,
                                $notify
                            );
                    }
                });
            });
    }

    /**
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @return PromiseInterface
     */
    public static function race($promisesOrValues)
    {
        return self::doResolve($promisesOrValues)
            ->then(function ($array) {
                if (!is_array($array) || !$array)
                {
                    return self::doResolve();
                }

                return new Promise(function($resolve, $reject, $cancel, $notify) use($array) {
                    foreach ($array as $promiseOrValue)
                    {
                        self::doResolve($promiseOrValue)
                            ->done($resolve, $reject, $cancel, $notify);
                    }
                });
            });
    }

    /**
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @return PromiseInterface
     */
    public static function any($promisesOrValues)
    {
        return self::some($promisesOrValues, 1)
            ->then(function($val) {
                return array_shift($val);
            });
    }

    /**
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @param int $howMany
     * @return PromiseInterface
     */
    public static function some($promisesOrValues, $howMany)
    {
        return self::doResolve($promisesOrValues)
            ->then(function($array) use($howMany) {
                if (!is_array($array) || !$array || $howMany < 1)
                {
                    return self::doResolve([]);
                }

                return new Promise(function($resolve, $reject, $cancel, $notify) use($array, $howMany) {
                    $len       = count($array);
                    $toResolve = min($howMany, $len);
                    $toReject  = ($len - $toResolve) + 1;
                    $values    = [];
                    $reasons   = [];

                    foreach ($array as $i=>$promiseOrValue)
                    {
                        $fulfiller = function($val) use($i, &$values, &$toResolve, $toReject, $resolve) {
                            if ($toResolve < 1 || $toReject < 1)
                            {
                                return;
                            }

                            $values[$i] = $val;

                            if (0 === --$toResolve)
                            {
                                $resolve($values);
                            }
                        };

                        $rejecter = function($reason) use($i, &$reasons, &$toReject, $toResolve, $reject) {
                            if ($toResolve < 1 || $toReject < 1)
                            {
                                return;
                            }

                            $reasons[$i] = $reason;

                            if (0 === --$toReject)
                            {
                                $reject($reasons);
                            }
                        };

                        self::doResolve($promiseOrValue)
                            ->done($fulfiller, $rejecter, $cancel, $notify);
                    }
                });
            });
    }

    /**
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @param callable $mapFunc
     * @return PromiseInterface
     */
    public static function map($promisesOrValues, callable $mapFunc)
    {
        return self::doResolve($promisesOrValues)
            ->then(function($array) use($mapFunc) {
                if (!is_array($array) || !$array)
                {
                    return self::doResolve([]);
                }

                return new Promise(function($resolve, $reject, $cancel, $notify) use($array, $mapFunc) {
                    $toResolve = count($array);
                    $values    = [];

                    foreach ($array as $i=>$promiseOrValue)
                    {
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
                                $cancel,
                                $notify
                            );
                    }
                });
            });
    }

    /**
     * @param PromiseInterface[]|mixed[] $promisesOrValues
     * @param callable $reduceFunc
     * @param PromiseInterface|mixed|null $initialValue
     * @return PromiseInterface
     */
    public static function reduce($promisesOrValues, callable $reduceFunc, $initialValue = null)
    {
        return self::doResolve($promisesOrValues)
            ->then(function($array) use($reduceFunc, $initialValue) {
                if (!is_array($array))
                {
                    $array = [];
                }

                $total = count($array);
                $i = 0;

                // Wrap the supplied $reduceFunc with one that handles promises and then delegates to the supplied.
                $wrappedReduceFunc = function($current, $val) use($reduceFunc, $total, &$i) {
                    return self::doResolve($current)
                        ->then(function($c) use($reduceFunc, $total, &$i, $val) {
                            return self::doResolve($val)
                                ->then(function($value) use($reduceFunc, $total, &$i, $c) {
                                    return $reduceFunc($c, $value, $i++, $total);
                                });
                        });
                };

                return array_reduce($array, $wrappedReduceFunc, $initialValue);
            });
    }
}
