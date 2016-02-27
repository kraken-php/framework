<?php

namespace Kraken\Supervision\Handler\Cmd;

use Kraken\Supervision\ErrorHandlerBase;
use Kraken\Supervision\ErrorHandlerInterface;
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
