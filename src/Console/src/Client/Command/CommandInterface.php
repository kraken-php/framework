<?php

namespace Kraken\Console\Client\Command;

interface CommandInterface
{
    /**
     * Check if method has been marked as async.
     *
     * @return bool
     */
    public function isAsync();
}
