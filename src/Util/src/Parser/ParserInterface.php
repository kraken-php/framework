<?php

namespace Kraken\Util\Parser;

interface ParserInterface
{
    /**
     * Encode the object to string.
     *
     * @param mixed $mixed
     * @return string
     */
    public function encode($mixed);

    /**
     * Decode the object from string.
     *
     * @param string $str
     * @return mixed
     */
    public function decode($str);
}
