<?php

namespace Kraken\Network\Socket;

interface SocketServerInterface
{
    /**
     * Stop server and free its resource.
     */
    public function stop();
}
