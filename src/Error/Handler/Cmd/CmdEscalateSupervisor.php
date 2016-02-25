<?php

namespace Kraken\Error\Handler\Cmd;

use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Promise\Promise;
use Error;
use Exception;

class CmdEscalateSupervisor extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        $this->runtime->fail($ex, $params);

        return Promise::doResolve('Runtime has handled failure.');
    }
}
