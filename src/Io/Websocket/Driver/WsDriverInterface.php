<?php

namespace Kraken\Io\Websocket\Driver;

use Kraken\Io\Websocket\Driver\Version\VersionManagerInterface;

interface WsDriverInterface extends VersionManagerInterface
{
    /**
     * Toggle whether to check encoding of incoming messages.
     *
     * @param bool
     */
    public function setEncodingChecks($opt);
}
