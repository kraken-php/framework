<?php

namespace Kraken\_Unit\Container\_Asset;

class Foo
{
    public $baz;

    public function __construct(Baz $baz)
    {
        $this->baz = $baz;
    }
}
