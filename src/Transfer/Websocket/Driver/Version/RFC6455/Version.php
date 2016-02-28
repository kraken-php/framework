<?php

namespace Kraken\Transfer\Websocket\Driver\Version\RFC6455;

use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\Http\HttpResponse;
use Kraken\Transfer\Http\HttpResponseInterface;
use Kraken\Transfer\IoMessageInterface;
use Kraken\Transfer\IoServerComponentInterface;
use Kraken\Transfer\IoConnectionInterface;
use Kraken\Transfer\Websocket\Driver\Version\VersionInterface;
use Ratchet\WebSocket\Encoding\ValidatorInterface;
use Ratchet\WebSocket\Version\RFC6455;
use StdClass;

/**
 * @link http://tools.ietf.org/html/rfc6455
 * @todo Unicode: return mb_convert_encoding(pack("N",$u), mb_internal_encoding(), 'UCS-4BE');
 */
class Version extends RFC6455 implements VersionInterface
{
    /**
     * @var HandshakeVerifier
     */
    protected $verifier;

    /**
     * @param ValidatorInterface|null $validator
     */
    public function __construct(ValidatorInterface $validator = null)
    {
        parent::__construct($validator);

        $this->verifier = new HandshakeVerifier();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->verifier);
    }

    /**
     * @param HttpRequestInterface $request
     * @return HttpResponseInterface
     */
    public function wsHandshake(HttpRequestInterface $request)
    {
        if ($this->verifier->verifyRequest($request) !== true)
        {
            return new HttpResponse(400);
        }

        return new HttpResponse(101, [
            'Upgrade'              => 'websocket',
            'Connection'           => 'Upgrade',
            'Sec-WebSocket-Accept' => $this->sign($request->getHeaderLine('Sec-WebSocket-Key'))
        ]);
    }

    /**
     * @param IoConnectionInterface $conn
     * @param IoServerComponentInterface $coalescedCallback
     * @return Connection
     */
    public function wsUpgrade(IoConnectionInterface $conn, IoServerComponentInterface $component)
    {
        $upgraded = new Connection($conn);

        if (!isset($upgraded->WebSocket))
        {
            $upgraded->WebSocket = new StdClass();
        }

        $upgraded->WebSocket->coalescedCallback = new OnMessageProxy([ $component, 'handleMessage' ]);

        return $upgraded;
    }

    /**
     * @param IoConnectionInterface $conn
     * @param string $message
     */
    public function wsMessage(IoConnectionInterface $conn, IoMessageInterface $message)
    {
        $this->onMessage($conn, $message->read());
    }

    /**
     * @override
     */
    public function isRequestSupported(HttpRequestInterface $request)
    {
        $version = (int)(string)$request->getHeaderLine('Sec-WebSocket-Version');

        return $this->getVersionNumber() === $version;
    }
}
