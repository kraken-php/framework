<?php

namespace Kraken\_Unit\Stream;

use Kraken\Stream\StreamReader;

class StreamReaderTest extends StreamSeekerTest
{
    public function testApiIsReadable_ReturnsTrue_ForReadableStream()
    {
        $stream = $this->createStreamMock();
        $this->assertTrue($stream->isReadable());
    }

    public function testApiIsReadable_ReturnsFalse_ForNotReadableStream()
    {
        $stream = $this->createStreamMock();
        $stream->close();
        $this->assertFalse($stream->isReadable());
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

    public function testApiRead_ReadsDataCorrectly()
    {
        $stream = $this->createStreamMock();
        $resource = $stream->getResource();

        $expectedData = "foobar\n";
        $capturedData = null;
        $capturedOrigin = null;

        $stream->on('data', function($origin, $data) use(&$capturedOrigin, &$capturedData) {
            $capturedOrigin = $origin;
            $capturedData = $data;
        });
        $stream->on('end', $this->expectCallableOnce());

        fwrite($resource, $expectedData);
        rewind($resource);

        $this->assertSame($expectedData, $stream->read());
        $this->assertSame($expectedData, $capturedData);
        $this->assertSame($stream, $capturedOrigin);
    }

    /**
     * @return StreamReader
     */
    protected function createStreamInjection($resource)
    {
        return new StreamReader($resource);
    }
}
