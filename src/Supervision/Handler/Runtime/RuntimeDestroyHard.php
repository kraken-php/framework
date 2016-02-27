<?php

namespace Kraken\Supervision\Handler\Runtime;

use Kraken\Supervision\SolverBase;
use Kraken\Supervision\SolverInterface;
use Kraken\Runtime\Runtime;
use Error;
use Exception;

class RuntimeDestroyHard extends SolverBase implements SolverInterface
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
    protected function handler($ex, $params = [])
    {
        $manager = $this->runtime->manager();

        return $manager->destroyRuntime($params['origin'], Runtime::DESTROY_FORCE_HARD);
    }
}
