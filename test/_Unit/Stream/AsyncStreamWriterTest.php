<?php

namespace Kraken\_Unit\Stream;

use Kraken\Loop\Model\SelectLoop;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopInterface;
use Kraken\Stream\AsyncStreamWriter;

class AsyncStreamWriterTest extends StreamSeekerTest
{
    public function testApiWrite_WritesDataProperly()
    {
        $loop = new Loop(new SelectLoop);
        $stream = $this->createAsyncStreamWriterMock(null, $loop);
        $resource = $stream->getResource();

        $expectedData = str_repeat('X', (int) $stream->getBufferSize()*1.5);

        $stream->on('drain', $this->expectCallableTwice());
        $stream->on('finish', $this->expectCallableOnce());

        $stream->write(substr($expectedData, 0, 1024));
        $stream->write(substr($expectedData, 1024));

        $loop->addTimer(1e-1, function() use($loop) {
            $loop->stop();
        });
        $loop->start();

        $stream->rewind();
        $this->assertSame($expectedData, fread($resource, (int) $stream->getBufferSize()*2));

        unset($loop);
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
