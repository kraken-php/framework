<?php

namespace Kraken\Util\Isolate;

use Kraken\Throwable\Exception\Runtime\ExecutionException;

interface IsolateInterface
{
    /**
     * Call function using detached isolated process.
     *
     * @param $func
     * @param array $params
     * @return string
     * @throws ExecutionException
     */
    public function call($func, $params = []);
}
