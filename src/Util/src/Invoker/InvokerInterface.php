<?php

namespace Kraken\Util\Invoker;

interface InvokerInterface
{
    /**
     * Call function $func with given arguments $args.
     *
     * @param string $func
     * @param mixed[] $args
     * @return mixed
     */
    public function call($func, $args = []);

    /**
     * Check if proxy for function $func exists.
     *
     * @param string $func
     * @return bool
     */
    public function existsProxy($func);

    /**
     * Set proxy for function $func.
     *
     * @param string $func
     * @param callable $callable
     */
    public function setProxy($func, $callable);

    /**
     * Remove proxy for function $func.
     *
     * @param string $func
     */
    public function removeProxy($func);

    /**
     * Get proxy for function $func.
     *
     * @param string $func
     * @return mixed
     */
    public function getProxy($func);
}
