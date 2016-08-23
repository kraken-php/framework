<?php

namespace Kraken\Transfer\Http;

use Kraken\Transfer\TransferComponentAwareInterface;
use Kraken\Transfer\Http\Driver\HttpDriver;
use Kraken\Transfer\Http\Driver\HttpDriverInterface;
use Kraken\Transfer\Null\NullServer;
use Kraken\Transfer\TransferMessageInterface;
use Kraken\Transfer\TransferComponentInterface;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Util\Buffer\Buffer;
use Error;
use Exception;

class HttpServer implements HttpServerInterface, TransferComponentAwareInterface
{
    /**
     * @var TransferComponentInterface
     */
    protected $httpServer;

    /**
     * @var HttpDriverInterface
     */
    protected $httpDriver;

    /**
     * @param TransferComponentAwareInterface|null $aware
     * @param TransferComponentInterface|null $component
     */
    public function __construct(TransferComponentAwareInterface $aware = null, TransferComponentInterface $component = null)
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
    public function setComponent(TransferComponentInterface $component = null)
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
    public function handleConnect(TransferConnectionInterface $conn)
    {
        $conn->httpBuffer = new Buffer();
        $conn->httpHeadersReceived = false;
        $conn->httpRequest = null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(TransferConnectionInterface $conn)
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
    public function handleMessage(TransferConnectionInterface $conn, TransferMessageInterface $message)
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
    public function handleError(TransferConnectionInterface $conn, $ex)
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
     * @param TransferConnectionInterface $conn
     * @param int $code
     * @return null
     */
    protected function close(TransferConnectionInterface $conn, $code = 400)
    {
        $response = new HttpResponse($code);

        $conn->send($response);
        $conn->close();
    }
}
