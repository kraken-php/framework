<?php

namespace Kraken\Runtime\Supervision\Runtime;

use Kraken\Promise\Promise;
use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
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
        $manager = $this->runtime->getManager();
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
