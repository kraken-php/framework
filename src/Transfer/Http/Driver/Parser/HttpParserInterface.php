<?php

namespace Kraken\Transfer\Http\Driver\Parser;

use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\Http\HttpResponseInterface;
use Exception;

interface HttpParserInterface
{
    /**
     * Parse given string and return HttpRequestInterface object.
     *
     * @param string $message
     * @return HttpRequestInterface
     * @throws Exception
     */
    public function parseRequest($message);

    /**
     * Parse given string and return HttpResponseInterface object.
     *
     * @param string $message
     * @return HttpResponseInterface
     * @throws Exception
     */
    public function parseResponse($message);
}
