<?php

namespace Kraken\Redis\Protocol\Data;

use Kraken\Redis\Protocol\Data\Arrays;
use Kraken\Redis\Protocol\Data\BulkStrings;
use Kraken\Redis\Protocol\Model\ModelInterface;
use Kraken\Redis\Protocol\Data\Serializer\SerializerInterface;

class Request implements ModelInterface
{
    private $command;
    private $args;

    public function __construct($command, array $args = array())
    {
        $this->command = $command;
        $this->args    = $args;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getReplyModel()
    {
        $models = array(new BulkStrings($this->command));
        foreach ($this->args as $arg) {
            $models []= new BulkStrings($arg);
        }

        return new Arrays($models);
    }

    public function raw()
    {
        $ret = $this->args;
        array_unshift($ret, $this->command);

        return $ret;
    }

    public function serialized(SerializerInterface $serializer)
    {
        return $serializer->getRequestMessage($this->command, $this->args);
    }
}
