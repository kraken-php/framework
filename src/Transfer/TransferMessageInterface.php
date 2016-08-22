<?php

namespace Kraken\Transfer;

interface TransferMessageInterface
{
    /**
     * Return original message as string.
     *
     * @return string
     */
    public function read();
}
