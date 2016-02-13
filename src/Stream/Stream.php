<?php

namespace Kraken\Stream;

use Kraken\Event\BaseEventEmitter;
use Kraken\Exception\Io\ReadException;
use Kraken\Exception\Io\WriteException;
use Kraken\Exception\Runtime\InvalidArgumentException;
use Kraken\Loop\LoopInterface;
use Kraken\Stream\Buffer\MemorySoftBuffer;
use Exception;

class Stream extends BaseEventEmitter implements StreamInterface
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var bool
     */
    protected $autoClose;

    /**
     * @var bool
     */
    protected $readable;

    /**
     * @var bool
     */
    protected $writable;

    /**
     * @var bool
     */
    protected $closing;

    /**
     * @var int
     */
    protected $bufferSize;

    /**
     * @var WritableStreamInterface
     */
    protected $buffer;

    /**
     * @param resource $resource
     * @param LoopInterface $loop
     * @param bool $autoClose
     * @throws InvalidArgumentException
     */
    public function __construct($resource, LoopInterface $loop, $autoClose = true)
    {
        if (!is_resource($resource))
        {
             throw new InvalidArgumentException('First parameter must be a valid resource.');
        }

        $this->resource = $resource;
        $this->loop = $loop;
        $this->autoClose = $autoClose;
        $this->readable = true;
        $this->writable = true;
        $this->closing = false;
        $this->bufferSize = 4096;
        $this->buffer = new MemorySoftBuffer($this->resource, $this->loop);

        if (function_exists('stream_set_blocking'))
        {
            stream_set_blocking($this->resource, 0);
        }

        if (function_exists('stream_set_read_buffer'))
        {
            stream_set_read_buffer($this->resource, 0);
        }

        $this->buffer->on('error', function(Exception $ex) {
            $this->emit('error', [ $ex ]);
            $this->close();
        });

        $this->buffer->on('drain', function() {
            $this->emit('drain');
        });

        $this->resume();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->handleClose();

        parent::__destruct();

        unset($this->resource);
        unset($this->loop);
        unset($this->autoClose);
        unset($this->readable);
        unset($this->writable);
        unset($this->closing);
        unset($this->buffer);
    }

    /**
     * @override
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @override
     */
    public function getMetadata()
    {
        return stream_get_meta_data($this->resource);
    }

    /**
     * @override
     */
    public function getStreamType()
    {
        return $this->getMetadata()['stream_type'];
    }

    /**
     * @override
     */
    public function getWrapperType()
    {
        return $this->getMetadata()['wrapper_type'];
    }

    /**
     * @override
     */
    public function isOpen()
    {
        return !$this->closing && ($this->writable || $this->readable);
    }

    /**
     * @override
     */
    public function isSeekable()
    {
        return $this->getMetadata()['seekable'];
    }

    /**
     * @override
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * @override
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * @override
     */
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
        $this->buffer->setBufferSize($bufferSize);
    }

    /**
     * @override
     */
    public function getBufferSize()
    {
        return $this->bufferSize;
    }

    /**
     * @override
     */
    public function tell()
    {
        if (!$this->isSeekable())
        {
            throw new ReadException('Cannt tell offset of this kind of stream.');
        }

        $ret = ftell($this->resource);
        if ($ret === false)
        {
            throw new ReadException('Cannot tell offset of stream.');
        }

        return $ret;
    }

    /**
     * @override
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable())
        {
            throw new WriteException('Cannt seek on this kind of stream.');
        }

        $pointer = fseek($this->resource, $offset, $whence);
        if ($pointer === false)
        {
            throw new WriteException('Cannot seek on stream.');
        }

        $this->emit('seek', [ $pointer ]);
    }

    /**
     * @override
     */
    public function rewind()
    {
        if (!$this->isSeekable())
        {
            throw new WriteException('Cannt rewind this kind of stream.');
        }

        if (false === rewind($this->resource))
        {
            throw new WriteException('Cannot rewind stream.');
        }
    }

    /**
     * @override
     */
    public function pause()
    {
        $this->loop->removeReadStream($this->resource);
    }

    /**
     * @override
     */
    public function resume()
    {
        if ($this->readable)
        {
            $this->loop->addReadStream($this->resource, [ $this, 'handleData' ]);
        }
    }

    /**
     * @override
     */
    public function write($text)
    {
        if (!$this->writable)
        {
            throw new WriteException('Stream is no longer writable.');
        }

        return $this->buffer->write($text);
    }

    /**
     * @override
     */
    public function read($length = null)
    {
        if (!$this->readable)
        {
            throw new ReadException('Stream is no longer readable.');
        }

        if ($length === null)
        {
            $length = $this->bufferSize;
        }

        $ret = fread($this->resource, $length);
        if ($ret === false)
        {
            throw new ReadException('Cannot read stream.');
        }

        return $ret;
    }

    /**
     * @override
     */
    public function close()
    {
        if ($this->closing)
        {
            return;
        }

        $this->closing = true;
        $this->readable = false;
        $this->writable = false;

        $this->emit('close');

        $this->loop->removeStream($this->resource);
        $this->handleClose();
    }

//    public function pipe(WritableStreamInterface $dest, array $options = array())
//    {
//        Util::pipe($this, $dest, $options);
//
//        return $dest;
//    }

    /**
     * Handle the incoming stream.
     *
     * @internal
     *
     * @param resource $resource
     */
    public function handleData($resource)
    {
        $data = fread($resource, $this->bufferSize);

        $this->emit('data', [ $data ]);

        if (!is_resource($resource) || feof($resource))
        {
            $this->close();
        }
    }

    /**
     * Handle the close of the stream object.
     *
     * @internal
     */
    public function handleClose()
    {
        if ($this->autoClose === true && is_resource($this->resource))
        {
            fclose($this->resource);
        }
    }
}
