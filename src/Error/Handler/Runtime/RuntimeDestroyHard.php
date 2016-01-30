<?php

namespace Kraken\Error\Handler\Runtime;

use Exception;
use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Runtime\Runtime;

class RuntimeDestroyHard extends ErrorHandlerBase implements ErrorHandlerInterface
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

        return $manager->destroyRuntime($params['origin'], Runtime::DESTROY_FORCE_HARD);
    }
}
