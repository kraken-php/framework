<?php

namespace Kraken\Runtime\Supervision\Container;

use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Error;
use Exception;

class ContainerDestroy extends Solver implements SolverInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function solver($ex, $params = [])
    {
        return $this->runtime->destroy();
    }
}
