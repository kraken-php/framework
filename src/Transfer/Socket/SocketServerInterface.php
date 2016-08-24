<?php

namespace Kraken\Transfer\Socket;

interface SocketServerInterface
{
    /**
     * Stop server and free its resource.
     */
    public function stop();
}
