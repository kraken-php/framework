<?php

namespace Kraken\SSH;

use Dazzle\Stream\AsyncStreamInterface;

interface SSH2ResourceInterface extends AsyncStreamInterface
{
    /**
     * @return string
     */
    public function getId();
}
