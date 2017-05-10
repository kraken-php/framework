<?php

namespace Kraken\Redis\Protocol\Model;

use Kraken\Redis\Protocol\Data\Serializer\SerializerInterface;

interface ModelInterface
{
    /**
     * Returns value of this model as a native representation for PHP
     *
     * @return mixed
     */
    public function raw();

    /**
     * Returns the serialized representation of this protocol message
     *
     * @param SerializerInterface $serializer;
     * @return string
     */
    public function serialized(SerializerInterface $serializer);
}