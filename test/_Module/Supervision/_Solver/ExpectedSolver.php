<?php

namespace Kraken\_Module\Supervision\_Solver;

use Kraken\Supervision\Solver;

class ExpectedSolver extends Solver
{
    /**
     * @override
     * @inheritDoc
     */
    protected function solver($ex, $params = [])
    {
        return $this;
    }
}
