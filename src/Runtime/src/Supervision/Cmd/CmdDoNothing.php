<?php

namespace Kraken\Runtime\Supervision\Cmd;

use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Error;
use Exception;

class CmdDoNothing extends Solver implements SolverInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function solver($ex, $params = [])
    {
        return null;
    }
}
