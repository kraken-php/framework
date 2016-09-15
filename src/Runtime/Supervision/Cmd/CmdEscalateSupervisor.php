<?php

namespace Kraken\Runtime\Supervision\Cmd;

use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Kraken\Promise\Promise;
use Error;
use Exception;

class CmdEscalateSupervisor extends Solver implements SolverInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function solver($ex, $params = [])
    {
        $this->runtime->fail($ex, $params);

        return Promise::doResolve('Runtime has handled failure.');
    }
}
