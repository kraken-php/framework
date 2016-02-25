<?php

namespace Kraken\Io\Http;

use Kraken\Io\Http\Driver\HttpDriver;
use Kraken\Io\Http\Driver\HttpDriverInterface;
use Kraken\Io\IoMessageInterface;
use Kraken\Io\IoServerComponentInterface;
use Kraken\Io\IoConnectionInterface;
use Kraken\Util\Buffer\Buffer;
use Error;
use Exception;

class HttpServer implements HttpServerInterface
{
    /**
     * @var IoServerComponentInterface
     */
    protected $httpServer;

    /**
     * @var HttpDriverInterface
     */
    protected $httpDriver;

    /**
     * @param IoServerComponentInterface $component
     */
    public function __construct(IoServerComponentInterface $component)
    {
        $this->httpServer = $component;
        $this->httpDriver = new HttpDriver();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->httpServer);
        unset($this->httpDriver);
    }

    /**
     * @override
     */
    public function getDriver()
    {
        return $this->httpDriver;
    }

    /**
     * @override
     */
    public function handleConnect(IoConnectionInterface $conn)
    {
        $conn->httpBuffer = new Buffer();
        $conn->httpHeadersReceived = false;
        $conn->httpRequest = null;
    }

    /**
     * @override
     */
    public function handleDisconnect(IoConnectionInterface $conn)
    {
        if ($conn->httpHeadersReceived)
        {
            $this->httpServer->handleDisconnect($conn);
        }
    }

    /**
     * @override
     */
    public function handleMessage(IoConnectionInterface $conn, IoMessageInterface $message)
    {
        if ($conn->httpHeadersReceived !== true)
        {
            try
            {
                if (($request = $this->httpDriver->readRequest($conn->httpBuffer, $message->read())) === null)
                {
                    return;
                }
            }
            catch (Error $ex)
            {
                return $this->close($conn, 413);
            }
            catch (Exception $ex)
            {
                return $this->close($conn, 413);
            }

            $conn->httpHeadersReceived = true;
            $conn->httpRequest = $request;

            $this->httpServer->handleConnect($conn);
            $this->httpServer->handleMessage($conn, $request);
        }
        else
        {
            $this->httpServer->handleMessage($conn, $message);
        }
    }

    /**
     * @override
     */
    public function handleError(IoConnectionInterface $conn, $ex)
    {
        if ($conn->httpHeadersReceived)
        {
            $this->httpServer->handleError($conn, $ex);
        }
        else
        {
            $this->close($conn, 500);
        }
    }

    /**
     * Close a connection with an HTTP response.
     *
     * @param IoConnectionInterface $conn
     * @param int $code
     * @return null
     */
    protected function close(IoConnectionInterface $conn, $code = 400)
    {
        $response = new HttpResponse($code);

        $conn->send((string)$response);
        $conn->close();
    }
}
