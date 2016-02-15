<?php

namespace Kraken\Test\Unit\Stream;

use Kraken\Loop\LoopInterface;
use Kraken\Stream\StreamAsync;
use Kraken\Test\Unit\TestCase;

class StreamAsyncTest extends TestCase
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
     * @return StreamAsync
     */
    private function createStreamMock($resource = null, $loop = null)
    {
        return new StreamAsync(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }

    /**
     * @return LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createWritableLoopMock()
    {
        $loop = $this->createLoopMock();
        $loop
            ->expects($this->once())
            ->method('addWriteStream')
            ->will($this->returnCallback(function($stream, $listener) {
                call_user_func($listener, $stream);
            }));

        return $loop;
    }

    /**
     * @return LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createLoopMock()
    {
        return $this->getMock('Kraken\Loop\LoopInterface');
    }
}
