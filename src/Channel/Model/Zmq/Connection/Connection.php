<?php

namespace Kraken\Channel\Model\Zmq\Connection;

class Connection
{
    /**
     * @var string
     */
    public $id;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
