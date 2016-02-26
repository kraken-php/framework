<?php

namespace Kraken\Util\Parser;

interface ParserInterface
{
    /**
     * Encodes object as string
     *
     * @param mixed $mixed
     * @return string
     */
    public function encode($mixed);

    /**
     * Decodes object from string
     *
     * @param string $str
     * @return mixed
     */
    public function decode($str);
}
