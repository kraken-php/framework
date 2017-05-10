<?php

namespace Kraken\Redis\Protocol\Data;

use Kraken\Redis\Protocol\Model\ModelInterface;
use Kraken\Redis\Protocol\Data\Serializer\SerializerInterface;
use Kraken\Redis\Protocol\Model\SimpleModel;

class Integers extends SimpleModel implements ModelInterface
{
    public function serialized(SerializerInterface $serializer)
    {
        return $serializer->integers($this->raw());
    }
}
