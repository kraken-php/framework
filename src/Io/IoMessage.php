<?php

namespace Kraken\Io;

class IoMessage implements IoMessageInterface
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
