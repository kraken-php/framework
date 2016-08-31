<?php

namespace Kraken\Runtime\Supervisor\Cmd;

use Kraken\Runtime\Supervisor\Solver;
use Kraken\Supervisor\SolverInterface;
use Error;
use Exception;

class CmdDoNothing extends Solver implements SolverInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        return null;
    }
}
