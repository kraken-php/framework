<?php

namespace Kraken\SSH;

use Kraken\Stream\AsyncStreamInterface;

interface SSH2ResourceInterface extends AsyncStreamInterface
{
    /**
     * @return string
     */
    public function getId();
}
