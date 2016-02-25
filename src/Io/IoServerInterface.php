<?php

namespace Kraken\Io;

use Kraken\Loop\LoopResourceInterface;

interface IoServerInterface extends LoopResourceInterface
{
    /**
     * Close the underlying SocketServerListner.
     */
    public function close();
}
