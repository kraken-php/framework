<?php

namespace Kraken\Transfer\Websocket;

use Kraken\Transfer\TransferComponentAwareInterface;
use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\Http\HttpResponse;
use Kraken\Transfer\Null\NullServer;
use Kraken\Transfer\Websocket\Driver\WsDriver;
use Kraken\Transfer\Websocket\Driver\WsDriverInterface;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\TransferMessageInterface;
use Kraken\Transfer\TransferComponentInterface;
use Error;
use Exception;
use SplObjectStorage;
use StdClass;

/**
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WsServer implements WsServerInterface, TransferComponentAwareInterface
{
    /**
     * @var TransferComponentInterface
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
     * @param TransferComponentAwareInterface|null $aware
     * @param TransferComponentInterface|null $component
     */
    public function __construct(TransferComponentAwareInterface $aware = null, TransferComponentInterface $component = null)
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
    public function setComponent(TransferComponentInterface $component = null)
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
    public function handleConnect(TransferConnectionInterface $conn)
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
    public function handleDisconnect(TransferConnectionInterface $conn)
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
    public function handleMessage(TransferConnectionInterface $conn, TransferMessageInterface $message)
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
    public function handleError(TransferConnectionInterface $conn, $ex)
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
    protected function attemptUpgrade(TransferConnectionInterface $conn)
    {
        $request = $conn->WebSocket->request;

        if (!$this->wsDriver->isVersionEnabled($request))
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

//        $agreedSubProtocols = $this->getSubProtocolString($request->getHeader('Sec-WebSocket-Protocol'));
//
//        if ($agreedSubProtocols !== '')
//        {
//            $response->setHeader('Sec-WebSocket-Protocol', $agreedSubProtocols);
//        }

        $conn->send((string) $response);

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
     * @override
     * @inheritDoc
     */
    public function getDriver()
    {
        return $this->wsDriver;
    }

//    /**
//     * @param string
//     * @return boolean
//     */
//    protected function isSubProtocolSupported($name)
//    {
//        if (!$this->isSpGenerated)
//        {
//            if ($this->component instanceof WsServerInterface)
//            {
//                $this->acceptedSubProtocols = array_flip($this->component->getSubProtocols());
//            }
//
//            $this->isSpGenerated = true;
//        }
//
//        return array_key_exists($name, $this->acceptedSubProtocols);
//    }
//
//    /**
//     * @param string[] $protocols
//     * @return string
//     */
//    protected function getSubProtocolString($protocols = [])
//    {
//        foreach ($protocols as $protocol)
//        {
//            if ($this->isSubProtocolSupported($protocol))
//            {
//                return $protocol;
//            }
//        }
//
//        return '';
//    }

    /**
     * Close a connection with an HTTP response.
     *
     * @param TransferConnectionInterface $conn
     * @param int $code
     * @return null
     */
    protected function close(TransferConnectionInterface $conn, $code = 400)
    {
        $response = new HttpResponse($code, [
            'Sec-WebSocket-Version' => $this->wsDriver->getVersionHeader()
        ]);

        $conn->send((string) $response);
        $conn->close();

        unset($conn->WebSocket);
    }
}
