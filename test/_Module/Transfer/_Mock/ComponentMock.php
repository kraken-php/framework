<?php

namespace Kraken\_Module\Transfer\_Mock;

use Error;
use Exception;
use Kraken\Event\BaseEventEmitter;
use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\ServerComponentInterface;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\TransferMessageInterface;

class ComponentMock extends BaseEventEmitter implements ServerComponentInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(TransferConnectionInterface $conn)
    {
        $this->emit('connect', [ $conn ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(TransferConnectionInterface $conn)
    {
        $this->emit('disconnect', [ $conn ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(TransferConnectionInterface $conn, TransferMessageInterface $message)
    {
        $this->emit('message', [ $conn, $message ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(TransferConnectionInterface $conn, $ex)
    {
        $this->emit('error', [ $conn, $ex ]);
    }
}
