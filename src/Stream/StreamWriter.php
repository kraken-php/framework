<?php

namespace Kraken\Stream;

use Kraken\Throwable\Io\WriteException;
use Kraken\Throwable\Runtime\InvalidArgumentException;

class StreamWriter extends StreamSeeker implements StreamWriterInterface
{
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

        $this->writable = true;
        $this->bufferSize = 4096;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->writable);
        unset($this->bufferSize);

        parent::__destruct();
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

        $this->emit('drain', [ $this ]);

        return true;
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

        $this->emit('close', [ $this ]);

        $this->handleClose();
    }
}
