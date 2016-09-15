<?php

namespace Kraken\Runtime\Supervision\Runtime;

use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
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
