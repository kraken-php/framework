<?php

namespace Kraken\Network\Websocket;

use Kraken\Network\NetworkComponentAwareInterface;
use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\Http\HttpResponse;
use Kraken\Network\Null\NullServer;
use Kraken\Network\Websocket\Driver\WsDriver;
use Kraken\Network\Websocket\Driver\WsDriverInterface;
use Kraken\Network\NetworkConnectionInterface;
use Kraken\Network\NetworkMessageInterface;
use Kraken\Network\NetworkComponentInterface;
use Error;
use Exception;
use SplObjectStorage;
use StdClass;

/**
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WsServer implements WsServerInterface, NetworkComponentAwareInterface
{
    /**
     * @var NetworkComponentInterface
     */
    protected $wsServer;

    /**
     * @var WsDriverInterface
     */
    protected $wsDriver;

    /**
     * @var SplObjectStorage
     */
    protected $connCollection;

    /**
     * @param NetworkComponentAwareInterface|null $aware
     * @param NetworkComponentInterface|null $component
     */
    public function __construct(NetworkComponentAwareInterface $aware = null, NetworkComponentInterface $component = null)
    {
        $this->wsServer = $component === null ? new NullServer() : $component;
        $this->wsDriver = new WsDriver();
        $this->connCollection = new SplObjectStorage();

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
        unset($this->wsServer);
        unset($this->wsDriver);
        unset($this->connCollection);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setComponent(NetworkComponentInterface $component = null)
    {
        $this->wsServer = $component === null ? new NullServer() : $component;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getComponent()
    {
        return $this->wsServer;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getDriver()
    {
        return $this->wsDriver;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(NetworkConnectionInterface $conn)
    {
        $conn->WebSocket = new StdClass();
        $conn->WebSocket->request     = $conn->httpRequest;
        $conn->WebSocket->established = false;
        $conn->WebSocket->closing     = false;

        $this->attemptUpgrade($conn);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(NetworkConnectionInterface $conn)
    {
        if ($this->connCollection->contains($conn))
        {
            $decor = $this->connCollection[$conn];
            $this->connCollection->detach($conn);

            $this->wsServer->handleDisconnect($decor);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message)
    {
        if ($message instanceof HttpRequestInterface)
        {
            return;
        }

        if ($conn->WebSocket->closing)
        {
            return;
        }

        if ($conn->WebSocket->established === true)
        {
            $conn->WebSocket->version->wsMessage($this->connCollection[$conn], $message);
            return;
        }

        $this->attemptUpgrade($conn);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(NetworkConnectionInterface $conn, $ex)
    {
        if ($conn->WebSocket->established && $this->connCollection->contains($conn))
        {
            $this->wsServer->handleError($this->connCollection[$conn], $ex);
        }
        else
        {
            $conn->close();
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function attemptUpgrade(NetworkConnectionInterface $conn)
    {
        $request = $conn->WebSocket->request;

        if (!$this->wsDriver->checkVersion($request))
        {
            return $this->close($conn);
        }

        $conn->WebSocket->version = $this->wsDriver->getVersion($request);

        try
        {
            $response = $conn->WebSocket->version->wsHandshake($request);
        }
        catch (Error $ex)
        {
            return;
        }
        catch (Exception $ex)
        {
            return;
        }

        $conn->send($response);

        if ($response->getStatusCode() !== 101)
        {
            return $conn->close();
        }

        $upgraded = $conn->WebSocket->version->wsUpgrade($conn, $this->wsServer);

        $this->connCollection->attach($conn, $upgraded);

        $upgraded->WebSocket->established = true;

        $this->wsServer->handleConnect($upgraded);
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
        $response = new HttpResponse($code, [
            'Sec-WebSocket-Version' => $this->wsDriver->getVersionHeader()
        ]);

        $conn->send($response);
        $conn->close();

        unset($conn->WebSocket);
    }
}
