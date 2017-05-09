<?php

namespace Kraken\Stream;

use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Runtime\WriteException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Loop\LoopAwareTrait;
use Kraken\Loop\LoopInterface;
use Kraken\Util\Buffer\Buffer;
use Kraken\Util\Buffer\BufferInterface;
use Error;
use Exception;

class AsyncStream extends Stream implements AsyncStreamInterface
{
    use LoopAwareTrait;

    /**
     * @var bool
     */
    protected $writing;

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
     * @var BufferInterface
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
        parent::__construct($resource, $autoClose);

        if (function_exists('stream_set_read_buffer'))
        {
            stream_set_read_buffer($this->resource, 0);
        }

        if (function_exists('stream_set_write_buffer'))
        {
            stream_set_write_buffer($this->resource, 0);
        }

        $this->loop = $loop;
        $this->writing = false;
        $this->reading = false;
        $this->readingStarted = false;
        $this->paused = true;
        $this->buffer = new Buffer();

        $this->resume();
    }

    /**
     *
     */
    public function __destruct()
    {
        parent::__destruct();

        unset($this->loop);
        unset($this->writing);
        unset($this->reading);
        unset($this->readingStarted);
        unset($this->paused);
        unset($this->buffer);
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
            $this->writing = false;
            $this->reading = false;
            $this->loop->removeWriteStream($this->resource);
            $this->loop->removeReadStream($this->resource);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resume()
    {
        if (($this->writable || $this->readable) && $this->paused)
        {
            $this->paused = false;

            if ($this->readable && $this->readingStarted)
            {
                $this->reading = true;
                $this->loop->addReadStream($this->resource, $this->getHandleReadFunction());
            }

            if ($this->writable && $this->buffer->isEmpty() === false)
            {
                $this->writing = true;
                $this->loop->addWriteStream($this->resource, $this->getHandleWriteFunction());
            }
        }
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

        $this->buffer->push($text);

        if (!$this->writing && !$this->paused)
        {
            $this->writing = true;
            $this->loop->addWriteStream($this->resource, $this->getHandleWriteFunction());
        }

        return $this->buffer->length() < $this->bufferSize;
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

        if ($this->buffer->isEmpty() === false)
        {
            $this->writeEnd();
        }

        $this->emit('close', [ $this ]);
        $this->handleClose();
        $this->emit('done', [ $this ]);
    }

    /**
     * Handle the outcoming stream.
     *
     * @internal
     */
    public function handleWrite()
    {
        $text = $this->buffer->peek();
        $sent = fwrite($this->resource, $text, $this->bufferSize);

        if ($sent === false)
        {
            $this->emit('error', [ $this, new WriteException('Error occurred while writing to the stream resource.') ]);
            return;
        }

        $lenBefore = strlen($text);
        $lenAfter = $lenBefore - $sent;
        $this->buffer->remove($sent);

        if ($lenAfter > 0 && $lenBefore >= $this->bufferSize && $lenAfter < $this->bufferSize)
        {
            $this->emit('drain', [ $this ]);
        }
        else if ($lenAfter === 0)
        {
            $this->loop->removeWriteStream($this->resource);
            $this->writing = false;
            $this->emit('drain', [ $this ]);
            $this->emit('finish', [ $this ]);
        }
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
     * Handle close.
     *
     * @internal
     */
    public function handleClose()
    {
        $this->pause();

        parent::handleClose();
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
     * Get function that should be invoked on write event.
     *
     * @return callable
     */
    protected function getHandleWriteFunction()
    {
        return [ $this, 'handleWrite' ];
    }

    /**
     *
     */
    private function writeEnd()
    {
        do
        {
            try
            {
                $sent = fwrite($this->resource, $this->buffer->peek());
                $this->buffer->remove($sent);
            }
            catch (Error $ex)
            {
                $sent = 0;
            }
            catch (Exception $ex)
            {
                $sent = 0;
            }
        }
        while (is_resource($this->resource) && $sent > 0 && !$this->buffer->isEmpty());
    }
}
