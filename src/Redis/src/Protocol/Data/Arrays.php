<?php

namespace Kraken\Redis\Protocol\Data;

use InvalidArgumentException;
use UnexpectedValueException;
use Kraken\Redis\Protocol\Data\Serializer\SerializerInterface;
use Kraken\Redis\Protocol\Model\ModelInterface;
use Kraken\Redis\Protocol\Model\SimpleModel;

class Arrays extends SimpleModel implements ModelInterface
{
    /**
     * @inheritDoc
     */
    public function __construct(array $value)
    {
        parent::__construct($value); // TODO: Change the autogenerated stub
    }

    /**
     * @overwrite
     * @return array|null
     */
    public function raw()
    {
        if (($value = parent::raw()) === null) {
            return null;
        }

        $ret = array();
        foreach ($value as $one) {
            if ($one instanceof ModelInterface) {
                $ret []= $one->raw();
            } else {
                $ret []= $one;
            }
        }
        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function serialized(SerializerInterface $serializer)
    {
        return $serializer->arrays($this->raw());
    }

    /**
     * Checks whether this model represents a valid unified request protocol message
     *
     * The new unified protocol was introduced in Redis 1.2, but it became the
     * standard way for talking with the Redis server in Redis 2.0. The unified
     * request protocol is what Redis already uses in replies in order to send
     * list of items to clients, and is called a Multi Bulk Reply.
     *
     * @return boolean
     * @link http://redis.io/topics/protocol
     */
    public function isRequest()
    {
        if (!($value = parent::raw())) {
            return false;
        }

        foreach ($value as $one) {
            if (!($one instanceof BulkStrings) && !is_string($one)) {
                return false;
            }
        }

        return true;
    }

    public function getRequestModel()
    {
        if (!($value = parent::raw())) {
            throw new UnexpectedValueException('Null-multi-bulk message can not be represented as a request, must contain string/bulk values');
        }

        $command = null;
        $args = array();

        foreach ($value as $one) {
            if ($one instanceof BulkStrings) {
                $one = $one->raw();
            } elseif (!is_string($one)) {
                throw new UnexpectedValueException('Message can not be represented as a request, must only contain string/bulk values');
            }

            if ($command === null) {
                $command = $one;
            } else {
                $args []= $one;
            }
        }

        return new Request($command, $args);
    }

}