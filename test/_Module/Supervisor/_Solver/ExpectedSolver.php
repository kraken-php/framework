<?php

namespace Kraken\_Module\Supervisor\_Solver;

use Kraken\Supervisor\SolverBase;

class ExpectedSolver extends SolverBase
{
    /**
     * @override
     * @inheritDoc
     */
    protected function handler($ex, $params = [])
    {
        return $this;
    }
}
