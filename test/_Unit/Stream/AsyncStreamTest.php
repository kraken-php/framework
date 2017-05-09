<?php

namespace Kraken\_Unit\Stream;

use Kraken\Loop\LoopInterface;
use Kraken\Stream\AsyncStream;

class AsyncStreamTest extends AsyncStreamWriterTest
{
    public function testApiPauseAndResumeAndIsPaused()
    {
        $stream = $this->createStreamMock();

        $this->assertFalse($stream->isPaused());
        $stream->pause();
        $this->assertTrue($stream->isPaused());
        $stream->resume();
        $this->assertFalse($stream->isPaused());
    }

    public function testApiWriteAndRead()
    {
        $stream = $this->createStreamMock(
            null,
            $this->createWritableLoopMock()
        );

        $expectedData = "foobar\n";
        $capturedData = null;
        $capturedOrigin = null;

        $stream->on('data', function($origin, $data) use(&$capturedOrigin, &$capturedData) {
            $capturedOrigin = $origin;
            $capturedData = $data;
        });
        $stream->on('drain', $this->expectCallableOnce());

        $stream->write($expectedData);
        $stream->rewind();
        $stream->handleRead($stream->getResource());

        $this->assertSame($expectedData, $capturedData);
        $this->assertSame($stream, $capturedOrigin);
    }

    public function testApiHandleRead_ReturnsProperHandler()
    {
        $stream = $this->createStreamMock();

        $expected = [ $stream, 'handleRead' ];
        $actual = $this->callProtectedMethod($stream, 'getHandleReadFunction');

        $this->assertSame($expected, $actual);
        $this->assertTrue(is_callable($expected));
    }

    public function testApiHandleWrite_ReturnsProperHandler()
    {
        $stream = $this->createStreamMock();

        $expected = [ $stream, 'handleWrite' ];
        $actual = $this->callProtectedMethod($stream, 'getHandleWriteFunction');

        $this->assertSame($expected, $actual);
        $this->assertTrue(is_callable($expected));
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface|null $loop
     * @return AsyncStream
     */
    protected function createStreamMock($resource = null, $loop = null)
    {
        return new AsyncStream(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }
}
