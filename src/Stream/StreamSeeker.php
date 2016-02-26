<?php

namespace Kraken\Stream;

use Kraken\Event\BaseEventEmitter;
use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Error;
use Exception;

class StreamSeeker extends BaseEventEmitter implements StreamSeekerInterface
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
    protected $closing;

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
        $this->closing = false;

        if (function_exists('stream_set_blocking'))
        {
            stream_set_blocking($this->resource, 0);
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
        unset($this->closing);
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
    public function getResourceId()
    {
        return (int) $this->resource;
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
        return !$this->closing;
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
    public function tell()
    {
        if (!$this->isSeekable())
        {
            throw new IoReadException('Cannt tell offset of this kind of stream.');
        }

        $ret = ftell($this->resource);
        if ($ret === false)
        {
            throw new IoReadException('Cannot tell offset of stream.');
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
            throw new IoWriteException('Cannt seek on this kind of stream.');
        }

        $pointer = fseek($this->resource, $offset, $whence);
        if ($pointer === false)
        {
            throw new IoWriteException('Cannot seek on stream.');
        }

        $this->emit('seek', [ $this, $pointer ]);
    }

    /**
     * @override
     */
    public function rewind()
    {
        if (!$this->isSeekable())
        {
            throw new IoWriteException('Cannt rewind this kind of stream.');
        }

        if (false === rewind($this->resource))
        {
            throw new IoWriteException('Cannot rewind stream.');
        }
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

    /**
     * Emit error event and the throws it too.
     *
     * @param Error|Exception $ex
     * @return null
     * @throws Error|Exception
     */
    protected function throwAndEmitException($ex)
    {
        $this->emit('error', [ $this, $ex ]);
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
