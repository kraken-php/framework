<?php

namespace Kraken\Runtime\Supervision\Cmd;

use Kraken\Runtime\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Kraken\Log\Logger;
use Kraken\Log\LoggerInterface;
use Error;
use Exception;

class CmdLog extends Solver implements SolverInterface
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

        $this->logger = $this->runtime->getCore()->make('Kraken\Log\LoggerInterface');
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
    protected function solver($ex, $params = [])
    {
        $this->logger->log(
            $this->context['level'],
            \Kraken\Throwable\Exception::toString($ex)
        );
    }
}
