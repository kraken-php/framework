<?php

namespace Kraken\Transfer;

interface IoMessageInterface
{
    /**
     * Return original message as string.
     *
     * @return string
     */
    public function read();
}
