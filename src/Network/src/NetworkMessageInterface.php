<?php

namespace Kraken\Network;

interface NetworkMessageInterface
{
    /**
     * Return original message as string.
     *
     * @return string
     */
    public function read();
}
