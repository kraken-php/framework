<?php

namespace Kraken\Error\Handler\Container;

use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Error;
use Exception;

class ContainerStart extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        return $this->runtime->start();
    }
}
