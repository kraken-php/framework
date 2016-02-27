<?php

namespace Kraken\Supervision\Handler\Container;

use Kraken\Supervision\SolverBase;
use Kraken\Supervision\SolverInterface;
use Error;
use Exception;

class ContainerContinue extends SolverBase implements SolverInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        $this->runtime->succeed();
    }
}
