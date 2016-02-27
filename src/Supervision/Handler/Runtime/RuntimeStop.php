<?php

namespace Kraken\Supervision\Handler\Runtime;

use Kraken\Supervision\ErrorHandlerBase;
use Kraken\Supervision\ErrorHandlerInterface;
use Error;
use Exception;

class RuntimeStop extends ErrorHandlerBase implements ErrorHandlerInterface
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
        return $this->runtime->manager()->stopRuntime($params['origin']);
    }
}
