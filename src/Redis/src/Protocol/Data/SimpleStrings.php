<?php

namespace Kraken\Redis\Protocol\Data;

use Kraken\Redis\Protocol\Model\SimpleModel;
use Kraken\Redis\Protocol\Model\ModelInterface;
use Kraken\Redis\Protocol\Data\Serializer\SerializerInterface;

class SimpleStrings extends SimpleModel implements ModelInterface
{
    /**
     * @inheritDoc
     */
    public function serialized(SerializerInterface $serializer)
    {
        return $serializer->simpleStrings($this->raw());
    }
}