<?php
namespace Kraken\Transfer\Socket;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListenerInterface;
use Kraken\Transfer\IoMessage;
use Kraken\Transfer\IoServerComponentInterface;
use Error;
use Exception;

class SocketServer implements SocketServerInterface
{
    /**
     * @var IoServerComponentInterface
     */
    protected $component;

    /**
     * @var SocketListenerInterface
     */
    protected $socket;

    /**
     * @param IoServerComponentInterface $component
     * @param SocketListenerInterface $socket
     */
    public function __construct(IoServerComponentInterface $component, SocketListenerInterface $socket)
    {
        $this->component = $component;
        $this->socket = $socket;

        $socket->on('connect', [ $this, 'handleConnect' ]);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->socket);
    }

    /**
     * Handler triggered when a new connection is received from SocketListener.
     *
     * @param SocketListenerInterface $server
     * @param SocketInterface $socket
     */
    public function handleConnect($server, $socket)
    {
        $socket->conn = new SocketConnection($socket);

        try
        {
            $this->component->handleConnect($socket->conn);

            $socket->on('data', [$this, 'handleData']);
            $socket->on('error', [$this, 'handleError']);
            $socket->on('close', [$this, 'handleDisconnect']);
        }
        catch (Error $ex)
        {
            $this->close($socket);
        }
        catch (Exception $ex)
        {
            $this->close($socket);
        }
    }

    /**
     * Handler triggered when a new data is received from existing connection.
     *
     * @param SocketInterface $socket
     * @param mixed $data
     */
    public function handleData($socket, $data)
    {
        try
        {
            $this->component->handleMEssage($socket->conn, new IoMessage($data));
        }
        catch (Error $ex)
        {
            $this->handleError($socket, $ex);
        }
        catch (Exception $ex)
        {
            $this->handleError($socket, $ex);
        }
    }

    /**
     * Handler triggered when an existing connection is being closed.
     *
     * @param SocketInterface $socket
     */
    public function handleDisconnect($socket)
    {
        try
        {
            $this->component->handleDisconnect($socket->conn);
        }
        catch (Error $ex)
        {
            $this->handleError($socket, $ex);
        }
        catch (Exception $ex)
        {
            $this->handleError($socket, $ex);
        }

        unset($socket->conn);
    }

    /**
     * Handler triggered when an error has occured during doing operation on existing connection.
     *
     * @param SocketInterface $socket
     * @param Error|Exception $ex
     */
    public function handleError($socket, $ex)
    {
        try
        {
            $this->component->handleError($socket->conn, $ex);
        }
        catch (Error $ex)
        {
            $this->close($socket);
        }
        catch (Exception $ex)
        {
            $this->close($socket);
        }
    }

    /**
     * @param SocketInterface $socket
     */
    protected function close(SocketInterface $socket)
    {
        $socket->close();
    }
}
