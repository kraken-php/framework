<?php

namespace Kraken\Runtime\Command\Cmd;

use Kraken\Runtime\Command\Command;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Supervisor\SupervisorInterface;
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
        $this->supervisor = $this->runtime->getCore()->make('Kraken\Runtime\Supervisor\SupervisorRemoteInterface');
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

        $this->handleException($origin, new $class($message));
    }

    /**
     * @param string $origin
     * @param Error|Exception $ex
     */
    protected function handleException($origin, $ex)
    {
        try
        {
            $this->supervisor->solve($ex, [ 'origin' => $origin ]);
        }
        catch (Error $ex)
        {
            $this->supervisor->solve($ex, [ 'origin' => $origin ]);
        }
        catch (Exception $ex)
        {
            $this->supervisor->solve($ex, [ 'origin' => $origin ]);
        }
    }
}
