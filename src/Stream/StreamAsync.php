<?php

namespace Kraken\Stream;

use Exception;
use Kraken\Exception\Io\WriteException;
use Kraken\Exception\Runtime\InvalidArgumentException;
use Kraken\Loop\LoopInterface;
use Kraken\Pattern\Buffer\BufferInterface;
use Kraken\Pattern\Buffer\BufferMemorySoft;

class StreamAsync extends Stream implements StreamAsyncInterface
{
    /**
     * @var LoopInterface
     */
    protected $loop;

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
        $this->buffer = new BufferMemorySoft();

//        $this->buffer->on('error', function(Exception $ex) {
//            $this->emit('error', [ $ex ]);
//            $this->close();
//        });
//
//        $this->buffer->on('drain', function() {
//            $this->emit('drain');
//        });

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

        $status = $this->buffer->write($text);

        if (!$this->listening)
        {
            $this->listening = true;
            $this->loop->addWriteStream($this->resource, [ $this, 'handleWrite' ]);
        }

        return $status;
    }

    /**
     * Handle the outcoming stream.
     *
     * @internal
     */
    public function handleWrite()
    {
        $text = $this->buffer->read();
        $sent = fwrite($this->resource, $text);

        if ($sent === false)
        {
            $this->emit('error', [ new WriteException('Error occurred while writing to the stream resource.') ]);
            return;
        }

        $lenBefore = strlen($text);
        $lenAfter = $lenBefore - $sent;
        $this->buffer->flush($sent);

        if ($lenAfter > 0 && $lenBefore >= $this->bufferSize && $lenAfter < $this->bufferSize)
        {
            $this->emit('drain');
        }
        else if ($lenAfter === 0)
        {
            $this->loop->removeWriteStream($this->resource);
            $this->listening = false;
            $this->emit('drain');
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

        if (!is_resource($this->resource) || feof($this->resource))
        {
            $this->close();
        }
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
