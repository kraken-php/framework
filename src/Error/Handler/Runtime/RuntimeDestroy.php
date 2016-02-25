<?php

namespace Kraken\Error\Handler\Runtime;

use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Runtime\Runtime;
use Error;
use Exception;

class RuntimeDestroy extends ErrorHandlerBase implements ErrorHandlerInterface
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

        return $manager->destroyRuntime($params['origin'], Runtime::DESTROY_FORCE);
    }
}
