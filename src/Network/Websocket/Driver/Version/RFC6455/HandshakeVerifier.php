<?php

namespace Kraken\Network\Websocket\Driver\Version\RFC6455;

use Kraken\Network\Http\HttpRequestInterface;
use Ratchet\WebSocket\Version\RFC6455\HandshakeVerifier as RatchetHandshakeVerifier;

class HandshakeVerifier extends RatchetHandshakeVerifier
{
    /**
     * @param HttpRequestInterface $request
     * @return bool
     */
    public function verifyRequest(HttpRequestInterface $request)
    {
        $passes = 0;

        $passes += (int)$this->verifyMethod($request->getMethod());
        $passes += (int)$this->verifyHTTPVersion($request->getProtocolVersion());
        $passes += (int)$this->verifyRequestURI($request->getUri()->getPath());
        $passes += (int)$this->verifyHost($request->getHeaderLine('Host'));
        $passes += (int)$this->verifyUpgradeRequest($request->getHeaderLine('Upgrade'));
        $passes += (int)$this->verifyConnection($request->getHeaderLine('Connection'));
        $passes += (int)$this->verifyKey($request->getHeaderLine('Sec-WebSocket-Key'));

        return (7 === $passes);
    }
}
