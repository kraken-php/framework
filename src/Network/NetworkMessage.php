<?php

namespace Kraken\Network;

class NetworkMessage implements NetworkMessageInterface
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->message);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function read()
    {
        return $this->message;
    }
}
