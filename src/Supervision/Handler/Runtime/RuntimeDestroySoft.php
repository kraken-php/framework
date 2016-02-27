<?php

namespace Kraken\Supervision\Handler\Runtime;

use Kraken\Supervision\ErrorHandlerBase;
use Kraken\Supervision\ErrorHandlerInterface;
use Kraken\Runtime\Runtime;
use Error;
use Exception;

class RuntimeDestroySoft extends ErrorHandlerBase implements ErrorHandlerInterface
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
