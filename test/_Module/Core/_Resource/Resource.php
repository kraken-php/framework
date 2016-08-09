<?php

namespace Kraken\_Module\Core\_Resource;

class Resource implements ResourceInterface
{
    /**
     * @var mixed[]
     */
    public $data = [];

    /**
     * @param mixed[] $data
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }
}
