<?php

namespace Kraken\_Unit\Stream;

use Kraken\Stream\StreamWriter;

class StreamWriterTest extends StreamSeekerTest
{
    public function testApiIsWritable_ReturnsTrue_ForWritableStream()
    {
        $stream = $this->createStreamMock();
        $this->assertTrue($stream->isWritable());
    }

    public function testApiIsWritable_ReturnsFalse_ForNotWritableStream()
    {
        $stream = $this->createStreamMock();
        $stream->close();
        $this->assertFalse($stream->isWritable());
    }

    public function testApiGetBufferSize_ReturnsBufferSize()
    {
        $stream = $this->createStreamMock();
        $this->assertEquals(4096, $stream->getBufferSize());
    }

    public function testApiSetBufferSize_SetsBufferSize()
    {
        $stream = $this->createStreamMock();
        $stream->setBufferSize(2048);
        $this->assertEquals(2048, $stream->getBufferSize());
    }

    public function testApiWrite_WritesDataCorrectly()
    {
        $stream = $this->createStreamMock();
        $resource = $stream->getResource();

        $expectedData = "foobar\n";
        $capturedData = null;

        $stream->on('drain', $this->expectCallableTwice());
        $stream->on('finish', $this->expectCallableTwice());

        $stream->write(substr($expectedData, 0, 2));
        $stream->write(substr($expectedData, 2));
        $stream->rewind();

        $this->assertSame($expectedData, fread($resource, $stream->getBufferSize()));
    }

    /**
     * @return StreamWriter
     */
    protected function createStreamInjection($resource)
    {
        return new StreamWriter($resource);
    }
}
