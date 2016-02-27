<?php

namespace Kraken\Supervision\Handler\Cmd;

use Kraken\Supervision\ErrorHandlerBase;
use Kraken\Supervision\ErrorHandlerInterface;
use Error;
use Exception;

class CmdDoNothing extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        return null;
    }
}
