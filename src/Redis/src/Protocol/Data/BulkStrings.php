<?php

namespace Kraken\Redis\Protocol\Data;

use Kraken\Redis\Protocol\Model\SimpleModel;
use Kraken\Redis\Protocol\Model\ModelInterface;
use Kraken\Redis\Protocol\Data\Serializer\SerializerInterface;

class BulkStrings extends SimpleModel implements ModelInterface
{
    public function serialized(SerializerInterface $serializer)
    {
        return $serializer->bulkStrings($this->raw());
    }
}
