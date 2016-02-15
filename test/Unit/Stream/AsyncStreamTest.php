<?php

namespace Kraken\Test\Unit\Stream;

use Kraken\Loop\LoopInterface;
use Kraken\Stream\AsyncStream;
use Kraken\Test\Unit\TestCase;

class AsyncStreamTest extends TestCase
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

        $stream->on('data', function($data) use(&$capturedData) {
            $capturedData = $data;
        });
        $stream->on('drain', $this->expectCallableOnce());

        $stream->write($expectedData);
        $stream->rewind();
        $stream->handleData($stream->getResource());

        $this->assertSame($expectedData, $capturedData);
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface|null $loop
     * @return AsyncStream
     */
    private function createStreamMock($resource = null, $loop = null)
    {
        return new AsyncStream(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }
}
