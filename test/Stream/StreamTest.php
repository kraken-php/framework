<?php

namespace Kraken\Test\Stream;

use Kraken\Exception\Runtime\InvalidArgumentException;
use Kraken\Stream\Stream;
use Kraken\Test\TestCase;

class StreamTest extends TestCase
{
    public function testConstructor()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $stream = new Stream($stream, $loop);
    }

    public function testConstructorThrowsExceptionOnInvalidStream()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $loop = $this->createLoopMock();

        $stream = new Stream('invalid', $loop);
    }

    public function testEventError()
    {

    }

    public function testEventClose()
    {

    }

    public function testEventData()
    {
        $stream = $this->createStreamMock();
        $resource = $stream->getResource();

        $expectedData = "foobar\n";
        $capturedData = null;

        $stream->on('data', function($data) use(&$capturedData) {
            $capturedData = $data;
        });

        fwrite($resource, $expectedData);
        rewind($resource);

        $stream->handleData($resource);
        $this->assertSame($expectedData, $capturedData);
    }

    public function testApiGetResource()
    {
        $resource = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $stream = new Stream($resource, $loop);

        $this->assertSame($resource, $stream->getResource());
    }

    public function testApiGetMetadata()
    {
        $resource = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $stream = new Stream($resource, $loop);

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

    public function testApiIsOpen()
    {
        $resource = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $stream = new Stream($resource, $loop);

        $this->assertTrue($stream->isOpen());
        $stream->close();
        $this->assertFalse($stream->isOpen());
    }

    public function testApiIsWritable()
    {
        $resource = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $stream = new Stream($resource, $loop);

        $this->assertTrue($stream->isWritable());
        $stream->close();
        $this->assertFalse($stream->isWritable());
    }

    public function testApiIsReadable()
    {
        $resource = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $stream = new Stream($resource, $loop);

        $this->assertTrue($stream->isReadable());
        $stream->close();
        $this->assertFalse($stream->isReadable());
    }

    public function testApiIsSeekable()
    {
        $loop = $this->createLoopMock();
        $seekableStream = new Stream(
            fopen('php://temp', 'r+'),
            $loop
        );
        $unseekableStream = new Stream(
            fopen('php://output', 'w'),
            $loop
        );

        $this->assertTrue($seekableStream->isSeekable());
        $this->assertFalse($unseekableStream->isSeekable());
    }

    public function testApiSetBufferSizeAndGetBufferSize()
    {
        $resource = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $stream = new Stream($resource, $loop);

        $this->assertEquals(4096, $stream->getBufferSize());
        $stream->setBufferSize(2048);
        $this->assertEquals(2048, $stream->getBufferSize());
    }

    public function testApiSeekAndTell()
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

    public function testApiRewindAndTell()
    {
        $stream = $this->createStreamMock();
        $resource = $stream->getResource();

        fwrite($resource, "0123456789");

        $this->assertEquals(10, $stream->tell());
        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
    }

    public function testApiPause()
    {
    }

    public function testApiResume()
    {
    }

    public function testApiWriteAndRead()
    {
        $resource = fopen('php://temp', 'r+');
        $loop = $this->createWritableLoopMock();
        $stream = new Stream($resource, $loop);

        $data = "foo\n";
        $stream->write($data);
        $stream->rewind();

        $this->assertSame($data, $stream->read());
    }

    public function testApiClose()
    {
        $resource = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $stream = new Stream($resource, $loop);

        $stream->on('close', $this->expectCallableOnce());
        $this->assertTrue($stream->isOpen());
        $this->assertEquals('stream', get_resource_type($resource));

        $stream->close();
        $this->assertEquals('Unknown', get_resource_type($resource));
        $this->assertFalse($stream->isOpen());
    }

    private function createStreamMock()
    {
        return new Stream(
            fopen('php://temp', 'r+'),
            $this->createLoopMock()
        );
    }

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

    private function createLoopMock()
    {
        return $this->getMock('Kraken\Loop\LoopInterface');
    }
}
