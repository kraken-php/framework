<?php

namespace Kraken\Stream;

use Kraken\Exception\Io\ReadException;
use Kraken\Exception\Io\WriteException;
use Kraken\Exception\Runtime\InvalidArgumentException;
use Exception;

class Stream extends StreamSeeker implements StreamInterface
{
    /**
     * @var bool
     */
    protected $readable;

    /**
     * @var bool
     */
    protected $writable;

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
        parent::__construct($resource, $autoClose);

        $this->readable = true;
        $this->writable = true;
        $this->bufferSize = 4096;

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
        unset($this->readable);
        unset($this->writable);
        unset($this->bufferSize);

        parent::__destruct();
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
    public function write($text)
    {
        if (!$this->writable)
        {
            return $this->throwAndEmitException(
                new WriteException('Stream is no longer writable.')
            );
        }

        $sent = fwrite($this->resource, $text);

        if ($sent === false)
        {
            return $this->throwAndEmitException(
                new WriteException('Error occurred while writing to the stream resource.')
            );
        }

        $this->emit('drain');

        return true;
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
        else if ($ret !== '')
        {
            $this->emit('data', [ $ret ]);
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

        $this->handleClose();
    }
}
