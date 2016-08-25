<?php

namespace Kraken\Network\Websocket;

use Kraken\Network\Websocket\Driver\WsDriverInterface;
use Kraken\Network\ServerComponentInterface;

interface WsServerInterface extends ServerComponentInterface
{
    /**
     * Return current driver
     *
     * @return WsDriverInterface
     */
    public function getDriver();
}
