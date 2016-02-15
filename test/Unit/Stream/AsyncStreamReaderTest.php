<?php

namespace Kraken\Test\Unit\Stream;

use Kraken\Loop\LoopInterface;
use Kraken\Stream\AsyncStreamReader;
use Kraken\Test\Unit\TestCase;

class AsyncStreamReaderTest extends TestCase
{
    public function testApiRead_ReadsDataProperly()
    {
        $stream = $this->createAsyncStreamReaderMock();
        $resource = $stream->getResource();

        $expectedData = "foobar\n";
        $capturedData = null;

        $stream->on('data', function($data) use(&$capturedData) {
            $capturedData = $data;
        });

        fwrite($resource, $expectedData);
        rewind($resource);
        $stream->handleData($stream->getResource());

        $this->assertSame($expectedData, $capturedData);
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface|null $loop
     * @return AsyncStreamReader
     */
    private function createAsyncStreamReaderMock($resource = null, $loop = null)
    {
        return new AsyncStreamReader(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }
}
