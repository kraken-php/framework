<?php

namespace Kraken\Runtime\Supervisor\Container;

use Kraken\Runtime\Supervisor\SolverBase;
use Kraken\Supervisor\SolverInterface;
use Error;
use Exception;

class ContainerStart extends SolverBase implements SolverInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        return $this->runtime->start();
    }
}
