<?php

namespace Kraken\Redis\Protocol\Data\Serializer;

use Clue\Redis\Protocol\Model\StatusReply;
use InvalidArgumentException;
use Exception;
use Kraken\Redis\Protocol\Data\Arrays;
use Kraken\Redis\Protocol\Data\BulkStrings;
use Kraken\Redis\Protocol\Data\SimpleStrings;
use Kraken\Redis\Protocol\Data\Errors;
use Kraken\Redis\Protocol\Data\Integers;
use Kraken\Redis\Command\Builder;
use Kraken\Redis\Protocol\Data\Request;
use Kraken\Redis\Protocol\Model\ModelInterface;

class RecursiveSerializer implements SerializerInterface
{
    public function getRequestMessage($command, array $args = array())
    {
        $data = '*' . (count($args) + 1) . "\r\n$" . strlen($command) . "\r\n" . $command . "\r\n";
        foreach ($args as $arg) {
            $data .= '$' . strlen($arg) . "\r\n" . $arg . "\r\n";
        }
        return $data;
    }

    public function createRequestModel($command, array $args = array())
    {
        return new Request($command, $args);
    }

    public function getReplyMessage($data)
    {
        if (is_string($data) || $data === null) {
            return $this->bulkStrings($data);
        } else if (is_int($data) || is_float($data) || is_bool($data)) {
            return $this->integers($data);
        } else if ($data instanceof Exception) {
            return $this->errors($data->getMessage());
        } else if (is_array($data)) {
            return $this->arrays($data);
        } else {
            throw new InvalidArgumentException('Invalid data type passed for serialization');
        }
    }

    public function createReplyModel($data)
    {
        if (is_string($data) || $data === null) {
            return new BulkStrings($data);
        } else if (is_int($data) || is_float($data) || is_bool($data)) {
            return new Integers($data);
        } else if ($data instanceof Exception) {
            return new Errors($data->getMessage());
        } else if (is_array($data)) {
            $models = array();
            foreach ($data as $one) {
                $models []= $this->createReplyModel($one);
            }
            return new Arrays($models);
        } else {
            throw new InvalidArgumentException('Invalid data type passed for serialization');
        }
    }

    public function bulkStrings($data)
    {
        if ($data === null) {
            /* null bulk reply */
            return '$-1' . self::CRLF;
        }
        /* bulk reply */
        return '$' . strlen($data) . self::CRLF . $data . self::CRLF;
    }

    public function errors($data)
    {
        /* error status reply */
        return '-' . $data . self::CRLF;
    }

    public function integers($data)
    {
        return ':' . (int)$data . self::CRLF;
    }

    public function arrays($data)
    {
        if ($data === null) {
            /* null multi bulk reply */
            return '*-1' . self::CRLF;
        }
        /* multi bulk reply */
        $ret = '*' . count($data) . self::CRLF;
        foreach ($data as $one) {
            if ($one instanceof ModelInterface) {
                $ret .= $one->serialized($this);
            } else {
                $ret .= $this->getReplyMessage($one);
            }
        }
        return $ret;
    }

    public function simpleStrings($data)
    {
        /* status reply */
        return '+' . $data . static::CRLF;
    }
}
