<?php

namespace Kraken\Redis\Protocol\Data;

use Exception;
use Kraken\Redis\Protocol\Model\ModelInterface;
use Kraken\Redis\Protocol\Data\Serializer\SerializerInterface;

/**
 *
 * @doc http://redis.io/topics/protocol#status-reply
 */
class Errors extends Exception implements ModelInterface
{
    /**
     * create error status reply (single line error message)
     *
     * @param string|ErrorReplyException $message
     * @return string
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function raw()
    {
        return $this->getMessage();
    }

    public function serialized(SerializerInterface $serializer)
    {
        return $serializer->errors($this->getMessage());
    }
}
