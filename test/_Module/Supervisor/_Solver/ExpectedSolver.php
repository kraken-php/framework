<?php

namespace Kraken\_Module\Supervisor\_Solver;

use Kraken\Supervisor\Solver;

class ExpectedSolver extends Solver
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
