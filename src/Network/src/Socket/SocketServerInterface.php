<?php

namespace Kraken\Network\Socket;

interface SocketServerInterface
{
    /**
     * Start server and alloc its resource.
     */
    public function start();
    /**
     * Stop server and free its resource.
     */
    public function stop();
}
