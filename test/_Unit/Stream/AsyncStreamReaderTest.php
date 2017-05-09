<?php

namespace Kraken\_Unit\Stream;

use Kraken\Loop\Loop;
use Kraken\Loop\LoopInterface;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Stream\AsyncStreamReader;

class AsyncStreamReaderTest extends StreamSeekerTest
{
    public function testApiRead_ReadsDataProperly()
    {
        if (substr(PHP_VERSION, 0, 3) === '5.6' && extension_loaded('xdebug'))
        {
            $this->markTestSkipped(
                'This test for some reason fails on Travis CI with PHP-5.6 and xdebug enabled and ONLY on master branch.'
            );
            return;
        }

        $loop = new Loop(new SelectLoop);
        $stream = $this->createAsyncStreamReaderMock(null, $loop);
        $resource = $stream->getResource();

        $expectedData = "foobar\n";
        $capturedData = null;
        $capturedOrigin = null;

        $stream->on('data', function($origin, $data) use(&$capturedOrigin, &$capturedData) {
            $capturedOrigin = $origin;
            $capturedData = $data;
        });
        $stream->on('end', $this->expectCallableOnce());
        $stream->read();

        fwrite($resource, $expectedData);
        rewind($resource);

        $loop->addTimer(1e-1, function() use($loop) {
            $loop->stop();
        });
        $loop->start();

        $this->assertSame($expectedData, $capturedData);
        $this->assertSame($stream, $capturedOrigin);

        unset($loop);
    }

    public function testApiHandleRead_ReturnsProperHandler()
    {
        $loop = new Loop(new SelectLoop);
        $stream = $this->createAsyncStreamReaderMock(null, $loop);

        $expected = [ $stream, 'handleRead' ];
        $actual = $this->callProtectedMethod($stream, 'getHandleReadFunction');

        $this->assertSame($expected, $actual);
        $this->assertTrue(is_callable($expected));
    }

    /**
     * @param resource|null $resource
     * @param LoopInterface|null $loop
     * @return AsyncStreamReader
     */
    protected function createAsyncStreamReaderMock($resource = null, $loop = null)
    {
        return new AsyncStreamReader(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource,
            is_null($loop) ? $this->createLoopMock() : $loop
        );
    }
}
