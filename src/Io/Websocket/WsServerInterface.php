<?php

namespace Kraken\Io\Websocket;

use Kraken\Io\IoServerComponentInterface;
use Kraken\Io\Websocket\Driver\WsDriverInterface;

interface WsServerInterface extends IoServerComponentInterface
{
    /**
     * Return current driver
     *
     * @return WsDriverInterface
     */
    public function getDriver();
}
