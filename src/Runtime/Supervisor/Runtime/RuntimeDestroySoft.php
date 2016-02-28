<?php

namespace Kraken\Runtime\Supervisor\Runtime;

use Kraken\Runtime\Supervisor\SolverBase;
use Kraken\Supervisor\SolverInterface;
use Kraken\Runtime\Runtime;
use Error;
use Exception;

class RuntimeDestroySoft extends SolverBase implements SolverInterface
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

        return $manager->destroyRuntime($params['origin'], Runtime::DESTROY_FORCE_SOFT);
    }
}
