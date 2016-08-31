<?php

namespace Kraken\Runtime\Supervisor\Runtime;

use Kraken\Runtime\Supervisor\Solver;
use Kraken\Supervisor\SolverInterface;
use Error;
use Exception;

class RuntimeStop extends Solver implements SolverInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'origin'
    ];

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function solver($ex, $params = [])
    {
        return $this->runtime->getManager()->stopRuntime($params['origin']);
    }
}
