<?php

namespace Kraken\_Unit\Container\_Asset;

class Invokable
{
    public $name = 'undefined';

    public $args = [];

    public function __invoke($name = 'undefined', $args = [])
    {
        $this->name = $name;
        $this->args = $args;

        return new Baz;
    }
}
