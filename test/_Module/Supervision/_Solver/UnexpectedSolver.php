<?php

namespace Kraken\_Module\Supervision\_Solver;

use Kraken\Supervision\Solver;

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
