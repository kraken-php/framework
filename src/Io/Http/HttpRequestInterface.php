<?php

namespace Kraken\Io\Http;

use Psr\Http\Message\RequestInterface;

interface HttpRequestInterface extends RequestInterface
{
    /**
     * Return request encoded as string.
     *
     * @return string
     */
    public function __toString();

    /**
     * Return request encoded as string.
     *
     * @return string
     */
    public function encode();
}
