<?php

namespace Kraken\Error\Handler\Cmd;

use Exception;
use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;

class CmdDoNothing extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler(Exception $ex, $params = [])
    {
        return null;
    }
}
