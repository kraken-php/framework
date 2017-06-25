<?php

namespace Kraken\Network\Http;

use Kraken\Network\NetworkComponentAwareInterface;
use Kraken\Network\Http\Driver\HttpDriver;
use Kraken\Network\Http\Driver\HttpDriverInterface;
use Kraken\Network\Null\NullServer;
use Kraken\Network\NetworkMessageInterface;
use Kraken\Network\NetworkComponentInterface;
use Kraken\Network\NetworkConnectionInterface;
use Dazzle\Util\Buffer\Buffer;
use Error;
use Exception;

class HttpServer implements HttpServerInterface, NetworkComponentAwareInterface
{
    /**
     * @var NetworkComponentInterface
     */
    protected $httpServer;

    /**
     * @var HttpDriverInterface
     */
    protected $httpDriver;

    /**
     * @param NetworkComponentAwareInterface|null $aware
     * @param NetworkComponentInterface|null $component
     */
    public function __construct(NetworkComponentAwareInterface $aware = null, NetworkComponentInterface $component = null)
    {
        $this->httpServer = $component;
        $this->httpDriver = new HttpDriver();

        if ($aware !== null)
        {
            $aware->setComponent($this);
        }
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
     * @inheritDoc
     */
    public function getDriver()
    {
        return $this->httpDriver;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setComponent(NetworkComponentInterface $component = null)
    {
        $this->httpServer = $component === null ? new NullServer() : $component;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getComponent()
    {
        return $this->httpServer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(NetworkConnectionInterface $conn)
    {
        $conn->httpBuffer = new Buffer();
        $conn->httpHeadersReceived = false;
        $conn->httpRequest = null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(NetworkConnectionInterface $conn)
    {
        if ($conn->httpHeadersReceived)
        {
            $this->httpServer->handleDisconnect($conn);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message)
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
     * @inheritDoc
     */
    public function handleError(NetworkConnectionInterface $conn, $ex)
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
     * @param NetworkConnectionInterface $conn
     * @param int $code
     * @return null
     */
    protected function close(NetworkConnectionInterface $conn, $code = 400)
    {
        $response = new HttpResponse($code);

        $conn->send($response);
        $conn->close();
    }
}
