<?php

namespace Kraken\Error\Handler\Cmd;

use Kraken\Error\ErrorHandlerBase;
use Kraken\Error\ErrorHandlerInterface;
use Kraken\Log\Logger;
use Kraken\Log\LoggerInterface;
use Error;
use Exception;

class CmdLog extends ErrorHandlerBase implements ErrorHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     *
     */
    protected function construct()
    {
        if (!isset($this->context['level']))
        {
            $this->context['level'] = Logger::EMERGENCY;
        }

        $this->logger = $this->runtime->core()->make('Kraken\Log\LoggerInterface');
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->logger);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function handler($ex, $params = [])
    {
        $this->logger->log(
            $this->context['level'], \Kraken\Exception\Exception::toString($ex)
        );
    }
}
