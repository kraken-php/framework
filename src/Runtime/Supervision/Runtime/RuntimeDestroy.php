<?php

namespace Kraken\Runtime\Supervision\Runtime;

use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Kraken\Runtime\Runtime;
use Error;
use Exception;

class RuntimeDestroy extends Solver implements SolverInterface
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
        $hash = isset($params['hash']) ? $params['hash'] : '';

        $manager = $this->runtime->getManager();

        return $manager->destroyRuntime($params['origin'], Runtime::DESTROY_FORCE, [ 'hash' => $hash ]);
    }
}
