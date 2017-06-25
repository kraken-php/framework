<?php

namespace Kraken\Runtime\Supervision\Runtime;

use Dazzle\Promise\Promise;
use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Dazzle\Throwable\Exception\Runtime\RejectionException;
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

        $hash  = isset($params['hash']) ? $params['hash'] : '';
        $alias = $params['origin'];

        if ($manager->existsThread($alias))
        {
            return $manager->createThread($alias, null, Runtime::CREATE_FORCE, [ 'hash' => $hash ]);
        }
        else if ($manager->existsProcess($alias))
        {
            return $manager->createProcess($alias, null, Runtime::CREATE_FORCE, [ 'hash' => $hash ]);
        }

        return Promise::doReject(new RejectionException("Runtime [$alias] does not exists."));
    }
}
