<?php

namespace Kraken\Network\Websocket\Driver;

use Kraken\Network\Websocket\Driver\Version\VersionManagerInterface;

interface WsDriverInterface extends VersionManagerInterface
{
    /**
     * Toggle whether to check encoding of incoming messages.
     *
     * @param bool
     */
    public function setEncodingChecks($opt);
}
