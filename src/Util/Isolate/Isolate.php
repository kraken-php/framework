<?php

namespace Kraken\Util\Isolate;

use Error;
use Exception;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;

class Isolate implements IsolateInterface
{
    /**
     * @var string
     */
    private $pid;

    /**
     * @var resource
     */
    private $socket;

    /**
     * @throws InstantiationException
     */
    public function __construct()
    {
        if (($sockets = @stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP)) === false)
        {
            throw new InstantiationException('stream_socket_pair() could not establish connection.');
        }

        $pid = pcntl_fork();

        if ($pid == -1)
        {
            throw new InstantiationException('pcntl_fork() could not create subprocess.');

        }
        else if ($pid)
        {
            $this->createParent($pid, $sockets);
        }
        else
        {
            $this->createChild($pid, $sockets);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        if (is_resource($this->socket))
        {
            fclose($this->socket);
        }

        unset($this->pid);
        unset($this->socket);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function call($func, $params = [])
    {
        $caller = [
            'func'   => $func,
            'params' => $params
        ];

        if (@fwrite($this->socket, json_encode($caller) . "\n") === false)
        {
            throw new ExecutionException('Could not created isolated call.');
        }

        if (($rep = @fgets($this->socket)) === false)
        {
            throw new ExecutionException('Could not receive returned value from isolated call.');
        }

        return rtrim($rep, "\n");
    }

    /**
     * @param int $pid
     * @param resource[] $sockets
     */
    private function createParent($pid, $sockets)
    {
        fclose($sockets[0]);

        $this->pid = (string) $pid;
        $this->socket = $sockets[1];
    }

    /**
     * @param int $pid
     * @param resource[] $sockets
     */
    private function createChild($pid, $sockets)
    {
        fclose($sockets[1]);

        $this->pid = (string) $pid;
        $this->socket = $sockets[0];

        while(($message = @fgets($this->socket)) !== false)
        {
            $ex = null;

            try
            {
                $data = json_decode($message, true);
                $return = (string) call_user_func_array($data['func'], $data['params']);
            }
            catch (Error $ex)
            {}
            catch (Exception $ex)
            {}

            if ($ex !== null || @fwrite($this->socket, $return . "\n") === false)
            {
                break;
            }
        }

        fclose($sockets[0]);
        exit(0);
    }
}
