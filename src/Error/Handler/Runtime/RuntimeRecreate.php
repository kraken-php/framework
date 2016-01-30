<?php

namespace Kraken\Error\Handler\Runtime;

use Exception;
use Kraken\Promise\Promise;
use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Exception\Runtime\RejectionException;
use Kraken\Runtime\Runtime;

class RuntimeRecreate extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'origin'
    ];

    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler(Exception $ex, $params = [])
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
