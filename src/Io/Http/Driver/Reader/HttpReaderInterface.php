<?php

namespace Kraken\Io\Http\Driver\Reader;

use Kraken\Io\Http\HttpRequestInterface;
use Kraken\Io\Http\HttpResponseInterface;
use Kraken\Util\Buffer\BufferInterface;
use Exception;

interface HttpReaderInterface
{
    /**
     * Read request or part of request using specified buffer and parse it to return HttpRequestInterface.
     *
     * @param BufferInterface $buffer
     * @param $data
     * @return HttpRequestInterface|null
     * @throws Exception
     */
    public function readRequest(BufferInterface $buffer, $data);

    /**
     * Read response or part of response using specified buffer and parse it to return HttpResponseInterface.
     *
     * @param BufferInterface $buffer
     * @param $data
     * @return HttpResponseInterface|null
     * @throws Exception
     */
    public function readResponse(BufferInterface $buffer, $data);
}
