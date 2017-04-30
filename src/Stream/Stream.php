<?php

namespace Kraken\Stream;

use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Runtime\WriteException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;

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
     * @inheritDoc
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBufferSize()
    {
        return $this->bufferSize;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function write($text = '')
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
        $this->emit('finish', [ $this ]);

        return true;
    }

    /**
     * @override
     * @inheritDoc
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
            $this->emit('data', [ $this, $ret ]);

            if (strlen($ret) < $length)
            {
                $this->emit('end', [ $this ]);
            }
        }

        return $ret;
    }

    /**
     * @override
     * @inheritDoc
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
        $this->emit('done', [ $this ]);
    }
}
