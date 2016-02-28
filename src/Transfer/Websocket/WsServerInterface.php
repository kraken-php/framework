<?php

namespace Kraken\Transfer\Websocket;

use Kraken\Transfer\Websocket\Driver\WsDriverInterface;
use Kraken\Transfer\IoServerComponentInterface;

interface WsServerInterface extends IoServerComponentInterface
{
    /**
     * Return current driver
     *
     * @return WsDriverInterface
     */
    public function getDriver();
}
