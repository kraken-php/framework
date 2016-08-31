<?php

namespace Kraken\Runtime\Supervisor\Runtime;

use Kraken\Promise\Promise;
use Kraken\Runtime\Supervisor\Solver;
use Kraken\Supervisor\SolverInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Kraken\Runtime\Runtime;
use Error;
use Exception;

class RuntimeRecreate extends Solver implements SolverInterface
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
        $manager = $this->runtime->manager();
        $alias = $params['origin'];

        if ($manager->existsThread($alias))
        {
            return $manager->createThread($alias, null, Runtime::CREATE_FORCE);
        }
        else if ($manager->existsProcess($alias))
        {
            return $manager->createProcess($alias, null, Runtime::CREATE_FORCE);
        }

        return Promise::doReject(new RejectionException("Runtime [$alias] does not exists."));
    }
}
