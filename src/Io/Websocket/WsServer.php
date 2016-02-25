<?php

namespace Kraken\Io\Websocket;

use Kraken\Io\Http\HttpRequestInterface;
use Kraken\Io\Http\HttpResponse;
use Kraken\Io\IoConnectionInterface;
use Kraken\Io\IoMessageInterface;
use Kraken\Io\Websocket\Driver\WsDriver;
use Kraken\Io\Websocket\Driver\WsDriverInterface;
use Kraken\Io\IoServerComponentInterface;
use Error;
use Exception;
use SplObjectStorage;
use StdClass;

/**
 * @link http://ca.php.net/manual/en/ref.http.php
 * @link http://dev.w3.org/html5/websockets/
 */
class WsServer implements WsServerInterface
{
    /**
     * @var IoServerComponentInterface
     */
    protected $wsServer;

    /**
     * @var WsDriverInterface
     */
    protected $wsDriver;

    /**
     * @var
     */
    protected $connectionCollection;

    /**
     * @param IoServerComponentInterface $component
     */
    public function __construct(IoServerComponentInterface $component)
    {
        $this->wsServer = $component;
        $this->wsDriver = new WsDriver();

        $this->connectionCollection = new SplObjectStorage();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->wsServer);
        unset($this->wsDriver);

        unset($this->connectionCollection);
    }

    /**
     * @override
     */
    public function handleConnect(IoConnectionInterface $conn)
    {
        $conn->WebSocket = new StdClass();
        $conn->WebSocket->request     = $conn->httpRequest;
        $conn->WebSocket->established = false;
        $conn->WebSocket->closing     = false;

        $this->attemptUpgrade($conn);
    }

    /**
     * @override
     */
    public function handleDisconnect(IoConnectionInterface $conn)
    {
        if ($this->connectionCollection->contains($conn))
        {
            $decor = $this->connectionCollection[$conn];
            $this->connectionCollection->detach($conn);

            $this->wsServer->handleDisconnect($decor);
        }
    }

    /**
     * @override
     */
    public function handleMessage(IoConnectionInterface $conn, IoMessageInterface $message)
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
            $conn->WebSocket->version->wsMessage($this->connectionCollection[$conn], $message);
            return;
        }

        $this->attemptUpgrade($conn);
    }

    /**
     * @override
     */
    public function handleError(IoConnectionInterface $conn, $ex)
    {
        if ($conn->WebSocket->established && $this->connectionCollection->contains($conn))
        {
            $this->wsServer->handleError($this->connectionCollection[$conn], $ex);
        }
        else
        {
            $conn->close();
        }
    }

    /**
     * @param IoConnectionInterface $conn
     */
    protected function attemptUpgrade(IoConnectionInterface $conn)
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

        $this->connectionCollection->attach($conn, $upgraded);

        $upgraded->WebSocket->established = true;

        $this->wsServer->handleConnect($upgraded);
    }

    /**
     * @override
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
     * @param IoConnectionInterface $conn
     * @param int $code
     * @return null
     */
    protected function close(IoConnectionInterface $conn, $code = 400)
    {
        $response = new HttpResponse($code, [
            'Sec-WebSocket-Version' => $this->wsDriver->getVersionHeader()
        ]);

        $conn->send((string) $response);
        $conn->close();

        unset($conn->WebSocket);
    }
}
