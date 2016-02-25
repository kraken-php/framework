<?php

namespace Kraken\Error;

use Kraken\Promise\PromiseInterface;
use Error;
use Exception;

interface ErrorHandlerInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function __invoke($ex, $params = []);

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function handle($ex, $params = []);
}
