<?php

namespace Kraken\Stream;

use Kraken\Event\BaseEventEmitter;
use Kraken\Exception\Io\ReadException;
use Kraken\Exception\Io\WriteException;
use Kraken\Exception\Runtime\InvalidArgumentException;
use Exception;

class StreamReader extends BaseEventEmitter implements StreamReadableInterface
{
    /**
     * @var resource
     */
    protected $resource;

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
    protected $closing;

    /**
     * @var int
     */
    protected $bufferSize;

    /**
     * @param resource $resource
     * @param bool $autoClose
     * @throws InvalidArgumentException
     */
    public function __construct($resource, $autoClose = true)
    {
        if (!is_resource($resource))
        {
             throw new InvalidArgumentException('First parameter must be a valid resource.');
        }

        $this->resource = $resource;
        $this->autoClose = $autoClose;
        $this->readable = true;
        $this->closing = false;
        $this->bufferSize = 4096;

        if (function_exists('stream_set_blocking'))
        {
            stream_set_blocking($this->resource, 0);
        }

        if (function_exists('stream_set_read_buffer'))
        {
            stream_set_read_buffer($this->resource, 0);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->handleClose();

        parent::__destruct();

        unset($this->resource);
        unset($this->autoClose);
        unset($this->readable);
        unset($this->closing);
        unset($this->bufferSize);
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
        return !$this->closing && $this->readable;
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
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
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
    public function read($length = null)
    {
        if (!$this->readable)
        {
            return $this->throwAndEmitException(
                new ReadException('Stream is no longer readable.')
            );
        }

        if ($length === null)
        {
            $length = $this->bufferSize;
        }

        $ret = fread($this->resource, $length);

        if ($ret === false)
        {
            return $this->throwAndEmitException(
                new ReadException('Cannot read stream.')
            );
        }

        $this->emit('data', [ $ret ]);

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

        $this->handleClose();
    }

    /**
     * Emit error event and the throws it too.
     *
     * @param Exception $ex
     * @return null
     * @throws Exception
     */
    protected function throwAndEmitException(Exception $ex)
    {
        $this->emit('error', [ $ex ]);
        throw $ex;
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
