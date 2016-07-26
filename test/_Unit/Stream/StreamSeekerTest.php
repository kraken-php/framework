<?php

namespace Kraken\_Unit\Stream;

use Kraken\Stream\StreamInterface;
use Kraken\Stream\StreamSeeker;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;

class StreamSeekerTest extends TUnit
{
    public function testConstructor()
    {
        $stream = $this->createStreamMock();
    }

    public function testConstructor_ThrowsException_OnInvalidStream()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $stream = $this->createStreamMock('invalid');
    }

    public function testApiGetResource_ReturnsValidResource()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = $this->createStreamMock($resource);
        $this->assertSame($resource, $stream->getResource());
    }

    public function testApiGetResourceId_ReturnsValidResourceId()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = $this->createStreamMock($resource);
        $this->assertSame((int)$resource, $stream->getResourceId());
    }

    public function testApiGetMetadata_ReturnsValidMetadata()
    {
        $stream = $this->createStreamMock();

        $expected = [
            'wrapper_type'  => 'PHP',
            'stream_type'   => 'TEMP',
            'mode'          => 'w+b',
            'unread_bytes'  => 0,
            'seekable'      => true,
            'uri'           => 'php://temp'
        ];

        $this->assertEquals($expected, $stream->getMetadata());
    }

    public function testApiIsOpen_ReturnsTrue_ForOpenStream()
    {
        $stream = $this->createStreamMock();
        $this->assertTrue($stream->isOpen());
    }

    public function testApiIsOpen_ReturnsFalse_ForNotOpenStream()
    {
        $stream = $this->createStreamMock();
        $stream->close();
        $this->assertFalse($stream->isOpen());
    }

    public function testApiIsSeekable_ReturnsTrue_ForSeekableStream()
    {
        $stream = $this->createStreamMock();
        $this->assertTrue($stream->isSeekable());
    }

    public function testApiIsSeekable_ReturnsFalse_ForNotSeekableStream()
    {
        $stream = $this->createStreamMock(fopen('php://output', 'w'));
        $this->assertFalse($stream->isSeekable());
    }

    public function testApiSeekAndTell_SetsAndGetsStreamOffset()
    {
        $stream = $this->createStreamMock();
        $resource = $stream->getResource();

        fwrite($resource, "0123456789");

        $this->assertEquals(10, $stream->tell());
        $stream->seek(5, SEEK_SET);
        $this->assertEquals(5, $stream->tell());
        $stream->seek(-2, SEEK_END);
        $this->assertEquals(8, $stream->tell());
        $stream->seek(-2, SEEK_CUR);
        $this->assertEquals(6, $stream->tell());
    }

    public function testApiRewind_ResetsStreamOffset()
    {
        $stream = $this->createStreamMock();
        $resource = $stream->getResource();

        fwrite($resource, "0123456789");

        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
    }

    public function testApiClose_ClosesStream()
    {
        $stream = $this->createStreamMock();
        $resource = $stream->getResource();

        $stream->on('close', $this->expectCallableOnce());
        $this->assertTrue($stream->isOpen());
        $this->assertEquals('stream', get_resource_type($resource));

        $stream->close();
        $this->assertEquals('Unknown', get_resource_type($resource));
        $this->assertFalse($stream->isOpen());
    }

    /**
     * @param resource|null $resource
     * @return StreamInterface
     */
    protected function createStreamMock($resource = null)
    {
        return $this->createStreamInjection(
            is_null($resource) ? fopen('php://temp', 'r+') : $resource
        );
    }

    /**
     * @return StreamSeeker
     */
    protected function createStreamInjection($resource)
    {
        return new StreamSeeker($resource);
    }
}
