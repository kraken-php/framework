<?php

namespace Kraken\Network\Http;

use Psr\Http\Message\ResponseInterface;

interface HttpResponseInterface extends ResponseInterface
{
    /**
     * Return response encoded as string.
     *
     * @return string
     */
    public function __toString();

    /**
     * Return response encoded as string.
     *
     * @return string
     */
    public function encode();
}
