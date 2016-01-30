<?php

namespace Kraken\Error\Handler\Cmd;

use Exception;
use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Promise\Promise;

class CmdEscalateSupervisor extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler(Exception $ex, $params = [])
    {
        $this->runtime->fail($ex, $params);

        return Promise::doResolve('Runtime has handled failure.');
    }
}
