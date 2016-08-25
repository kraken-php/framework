<?php

namespace Kraken\Network\Websocket\Driver\Version\HyBi10;

use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\Websocket\Driver\Version\RFC6455\Version as VersionRFC6455;
use Kraken\Network\Websocket\Driver\Version\VersionInterface;

class Version extends VersionRFC6455 implements VersionInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function isRequestSupported(HttpRequestInterface $request)
    {
        $version = (int)(string)$request->getHeaderLine('Sec-WebSocket-Version');

        return ($version >= 6 && $version < 13);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVersionNumber()
    {
        return 6;
    }
}
