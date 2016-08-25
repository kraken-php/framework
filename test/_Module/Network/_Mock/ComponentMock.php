<?php

namespace Kraken\_Module\Network\_Mock;

use Error;
use Exception;
use Kraken\Event\BaseEventEmitter;
use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\ServerComponentInterface;
use Kraken\Network\NetworkConnectionInterface;
use Kraken\Network\NetworkMessageInterface;

class ComponentMock extends BaseEventEmitter implements ServerComponentInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(NetworkConnectionInterface $conn)
    {
        $this->emit('connect', [ $conn ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(NetworkConnectionInterface $conn)
    {
        $this->emit('disconnect', [ $conn ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message)
    {
        $this->emit('message', [ $conn, $message ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(NetworkConnectionInterface $conn, $ex)
    {
        $this->emit('error', [ $conn, $ex ]);
    }
}
