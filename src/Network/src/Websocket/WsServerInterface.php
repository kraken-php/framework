<?php

namespace Kraken\Network\Websocket;

use Kraken\Network\Websocket\Driver\WsDriverInterface;
use Kraken\Network\NetworkComponentInterface;

interface WsServerInterface extends NetworkComponentInterface
{
    /**
     * Return current driver
     *
     * @return WsDriverInterface
     */
    public function getDriver();
}
