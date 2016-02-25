<?php

namespace Kraken\Io;

interface IoMessageInterface
{
    /**
     * Return original message as string.
     *
     * @return string
     */
    public function read();
}
