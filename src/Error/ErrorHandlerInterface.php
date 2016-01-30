<?php

namespace Kraken\Error;

use Exception;
use Kraken\Promise\PromiseInterface;

interface ErrorHandlerInterface
{
    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function __invoke(Exception $ex, $params = []);

    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function handle(Exception $ex, $params = []);
}
