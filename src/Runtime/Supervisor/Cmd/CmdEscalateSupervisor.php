<?php

namespace Kraken\Runtime\Supervisor\Cmd;

use Kraken\Runtime\Supervisor\SolverBase;
use Kraken\Supervisor\SolverInterface;
use Kraken\Promise\Promise;
use Error;
use Exception;

class CmdEscalateSupervisor extends SolverBase implements SolverInterface
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
