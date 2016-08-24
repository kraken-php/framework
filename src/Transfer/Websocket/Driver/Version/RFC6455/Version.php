<?php

namespace Kraken\Transfer\Websocket\Driver\Version\RFC6455;

use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\Http\HttpResponse;
use Kraken\Transfer\Http\HttpResponseInterface;
use Kraken\Transfer\TransferMessageInterface;
use Kraken\Transfer\TransferComponentInterface;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\Websocket\Driver\Version\VersionInterface;
use Ratchet\WebSocket\Encoding\ValidatorInterface;
use Ratchet\WebSocket\Version\RFC6455;
use StdClass;

/**
 * @link http://tools.ietf.org/html/rfc6455
 * @TODO KRF-345
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
     * @param TransferConnectionInterface $conn
     * @param TransferComponentInterface $coalescedCallback
     * @return Connection
     */
    public function wsUpgrade(TransferConnectionInterface $conn, TransferComponentInterface $component)
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
     * @param TransferConnectionInterface $conn
     * @param string $message
     */
    public function wsMessage(TransferConnectionInterface $conn, TransferMessageInterface $message)
    {
        $this->onMessage($conn, $message->read());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isRequestSupported(HttpRequestInterface $request)
    {
        $version = (int)(string)$request->getHeaderLine('Sec-WebSocket-Version');

        return $this->getVersionNumber() === $version;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVersionNumber()
    {
        return 13;
    }
}
