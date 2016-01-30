<?php

namespace Kraken\Error\Handler\Container;

use Exception;
use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;

class ContainerContinue extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler(Exception $ex, $params = [])
    {
        $this->runtime->succeed();
    }
}
