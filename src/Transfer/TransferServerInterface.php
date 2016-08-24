<?php

namespace Kraken\Transfer;

use Kraken\Loop\LoopResourceInterface;

interface TransferServerInterface extends LoopResourceInterface
{
    /**
     * Close the underlying SocketListner.
     */
    public function stop();

    /**
     * Close the underlying SocketListner.
     */
    public function close();
}
