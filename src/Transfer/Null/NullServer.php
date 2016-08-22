<?php

namespace Kraken\Transfer\Null;

use Kraken\Transfer\TransferMessageInterface;
use Kraken\Transfer\TransferComponentInterface;
use Kraken\Transfer\TransferConnectionInterface;

class NullServer implements TransferComponentInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(TransferConnectionInterface $conn)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(TransferConnectionInterface $conn)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(TransferConnectionInterface $conn, TransferMessageInterface $message)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(TransferConnectionInterface $conn, $ex)
    {}
}
