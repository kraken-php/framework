<?php

namespace Kraken\Transfer\Websocket;

use Kraken\Transfer\Websocket\Driver\WsDriverInterface;
use Kraken\Transfer\TransferComponentInterface;

interface WsServerInterface extends TransferComponentInterface
{
    /**
     * Return current driver
     *
     * @return WsDriverInterface
     */
    public function getDriver();
}
