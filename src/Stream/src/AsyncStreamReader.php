<?php

namespace Kraken\Stream;

use Kraken\Loop\LoopAwareTrait;
use Kraken\Loop\LoopInterface;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Error;
use Exception;

class AsyncStreamReader extends StreamReader implements AsyncStreamReaderInterface
{
    use LoopAwareTrait;

    /**
     * @var bool
     */
    protected $reading;

    /**
     * @var bool
     */
    protected $readingStarted;

    /**
     * @var bool
     */
    protected $paused;

    /**
     * @param resource $resource
     * @param LoopInterface $loop
     * @param bool $autoClose
     * @throws InvalidArgumentException
     */
    public function __construct($resource, LoopInterface $loop, $autoClose = true)
    {
        parent::__construct($resource, $autoClose);

        if (function_exists('stream_set_read_buffer'))
        {
            stream_set_read_buffer($this->resource, 0);
        }

        $this->loop = $loop;
        $this->reading = false;
        $this->readingStarted = false;
        $this->paused = true;

        $this->resume();
    }

    /**
     *
     */
    public function __destruct()
    {
        parent::__destruct();

        unset($this->loop);
        unset($this->reading);
        unset($this->readingStarted);
        unset($this->paused);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPaused()
    {
        return $this->paused;
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
    public function pause()
    {
        if (!$this->paused)
        {
            $this->paused = true;
            $this->reading = false;
            $this->loop->removeReadStream($this->resource);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resume()
    {
        if ($this->readable && $this->paused)
        {
            $this->paused = false;
            if ($this->readingStarted)
            {
                $this->reading = true;
                $this->loop->addReadStream($this->resource, $this->getHandleReadFunction());
            }
        }
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

        if (!$this->reading && !$this->paused)
        {
            $this->reading = true;
            $this->readingStarted = true;
            $this->loop->addReadStream($this->resource, $this->getHandleReadFunction());
        }

        return '';
    }

    /**
     * Handle the incoming stream.
     *
     * @internal
     */
    public function handleRead()
    {
        $length = $this->bufferSize;
        $ret = fread($this->resource, $length);

        if ($ret === false)
        {
            $this->emit('error', [ $this, new ReadException('Error occurred while reading from the stream resource.') ]);
            return;
        }

        if ($ret !== '')
        {
            $this->emit('data', [ $this, $ret ]);

            if (strlen($ret) < $length)
            {
                $this->loop->removeReadStream($this->resource);
                $this->reading = false;
                $this->emit('end', [ $this ]);
            }
        }
    }

    /**
     * Get function that should be invoked on read event.
     *
     * @return callable
     */
    protected function getHandleReadFunction()
    {
        return [ $this, 'handleRead' ];
    }

    /**
     * Handle close.
     *
     * @internal
     */
    public function handleClose()
    {
        $this->pause();

        parent::handleClose();
    }
}
