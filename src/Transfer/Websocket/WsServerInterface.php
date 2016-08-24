<?php

namespace Kraken\Transfer\Websocket;

use Kraken\Transfer\Websocket\Driver\WsDriverInterface;
use Kraken\Transfer\ServerComponentInterface;

interface WsServerInterface extends ServerComponentInterface
{
    /**
     * Return current driver
     *
     * @return WsDriverInterface
     */
    public function getDriver();
}
