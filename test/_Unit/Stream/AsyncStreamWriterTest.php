<?php

namespace Kraken\_Unit\Stream;

use Kraken\Loop\LoopInterface;
use Kraken\Stream\AsyncStreamWriter;

class AsyncStreamWriterTest extends StreamSeekerTest
{
    public function testApiWrite_WritesDataProperly()
    {
        $stream = $this->createAsyncStreamWriterMock();
        $resource = $stream->getResource();

        $expectedData = "foobar\n";

        $stream->on('drain', $this->expectCallableOnce());

        $stream->write($expectedData);
        $stream->rewind();

        $this->assertSame($expectedData, fread($resource, $stream->getBufferSize()));
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface|null $loop
     * @return AsyncStreamWriter
     */
    protected function createAsyncStreamWriterMock($resource = null, $loop = null)
    {
        return new AsyncStreamWriter(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource,
            is_null($loop) ? $this->createWritableLoopMock() : $loop
        );
    }
}
