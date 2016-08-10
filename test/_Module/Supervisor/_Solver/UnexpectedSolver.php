<?php

namespace Kraken\_Module\Supervisor\_Solver;

use Kraken\Supervisor\SolverBase;

class UnexpectedSolver extends SolverBase
{
    /**
     * @override
     * @inheritDoc
     */
    protected function handler($ex, $params = [])
    {
        return null;
    }
}
