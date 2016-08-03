<?php

namespace Kraken\_Unit\Container\_Asset;

class Bar
{
    public $baz;

    public function __construct(BazInterface $baz)
    {
        $this->baz = $baz;
    }
}
