<?php

namespace Kraken\Stream;

use Exception;
use Kraken\Exception\Io\WriteException;
use Kraken\Exception\Runtime\InvalidArgumentException;
use Kraken\Loop\LoopAwareTrait;
use Kraken\Loop\LoopInterface;
use Kraken\Pattern\Buffer\Buffer;
use Kraken\Pattern\Buffer\BufferInterface;

class AsyncStream extends Stream implements AsyncStreamInterface
{
    use LoopAwareTrait;

    /**
     * @var bool
     */
    protected $listening;

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

        $this->loop = $loop;
        $this->listening = false;
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
        unset($this->listening);
        unset($this->paused);
        unset($this->buffer);
    }

    /**
     * @override
     */
    public function isPaused()
    {
        return $this->paused;
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
    public function pause()
    {
        if (!$this->paused)
        {
            $this->paused = true;
            $this->loop->removeReadStream($this->resource);
        }
    }

    /**
     * @override
     */
    public function resume()
    {
        if ($this->readable && $this->paused)
        {
            $this->paused = false;
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
            return $this->throwAndEmitException(
                new WriteException('Stream is no longer writable.')
            );
        }

        $this->buffer->push($text);

        if (!$this->listening)
        {
            $this->listening = true;
            $this->loop->addWriteStream($this->resource, [ $this, 'handleWrite' ]);
        }

        return $this->buffer->length() < $this->bufferSize;
    }

    /**
     * Handle the outcoming stream.
     *
     * @internal
     */
    public function handleWrite()
    {
        $text = $this->buffer->peek();
        $sent = fwrite($this->resource, $text);

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
            $this->listening = false;
            $this->emit('drain', [ $this ]);
        }
    }

    /**
     * Handle the incoming stream.
     *
     * @internal
     */
    public function handleData()
    {
        try
        {
            $this->read();
        }
        catch (Exception $ex)
        {}
    }

    /**
     * @override
     */
    public function handleClose()
    {
        $this->pause();

        parent::handleClose();
    }
}
