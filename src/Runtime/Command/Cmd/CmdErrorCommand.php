<?php

namespace Kraken\Runtime\Command\Cmd;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Supervision\SupervisorInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Error;
use Exception;

class CmdErrorCommand extends Command implements CommandInterface
{
    /**
     * @var SupervisorInterface
     */
    protected $supervisor;

    /**
     * @override
     * @inheritDoc
     */
    protected function construct()
    {
        $this->supervisor = $this->runtime->getCore()->make('Kraken\Runtime\Supervision\SupervisorRemoteInterface');
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function destruct()
    {
        unset($this->supervisor);
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function command($params = [])
    {
        if (!isset($params['exception']) || !isset($params['message']) || !isset($params['origin']))
        {
            throw new RejectionException('Invalid params.');
        }

        $class   = $params['exception'];
        $message = $params['message'];
        $origin  = $params['origin'];
        $hash    = isset($params['hash']) ? $params['hash'] : '';

        $this->handleException(new $class($message), [
            'origin' => $origin,
            'hash'   => $hash
        ]);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     */
    protected function handleException($ex, $params)
    {
        try
        {
            $this->supervisor->solve($ex, $params);
        }
        catch (Error $ex)
        {
            $this->supervisor->solve($ex, $params);
        }
        catch (Exception $ex)
        {
            $this->supervisor->solve($ex, $params);
        }
    }
}
