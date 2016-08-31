<?php

namespace Kraken\_Module\Supervisor\_Solver;

use Kraken\Supervisor\Solver;

class UnexpectedSolver extends Solver
{
    /**
     * @override
     * @inheritDoc
     */
    protected function solver($ex, $params = [])
    {
        return null;
    }
}
