<?php

namespace Kraken\Supervision\Handler\Container;

use Kraken\Supervision\ErrorHandlerBase;
use Kraken\Supervision\ErrorHandlerInterface;
use Error;
use Exception;

class ContainerContinue extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        $this->runtime->succeed();
    }
}
