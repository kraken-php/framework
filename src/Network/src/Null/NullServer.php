<?php

namespace Kraken\Network\Null;

use Kraken\Network\NetworkMessageInterface;
use Kraken\Network\NetworkComponentInterface;
use Kraken\Network\NetworkConnectionInterface;

class NullServer implements NetworkComponentInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(NetworkConnectionInterface $conn)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(NetworkConnectionInterface $conn)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(NetworkConnectionInterface $conn, $ex)
    {}
}
