<?php

namespace Kraken\Transfer\Websocket\Driver;

use Kraken\Transfer\Websocket\Driver\Version\VersionManagerInterface;

interface WsDriverInterface extends VersionManagerInterface
{
    /**
     * Toggle whether to check encoding of incoming messages.
     *
     * @param bool
     */
    public function setEncodingChecks($opt);
}
