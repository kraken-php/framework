<?php

namespace Kraken\_Unit\Container\_Asset;

class Bax
{
    public $baz;

    public $bar;

    public function __construct(Baz $baz, Bar $bar)
    {
        $this->baz = $baz;
        $this->bar = $bar;
    }
}
