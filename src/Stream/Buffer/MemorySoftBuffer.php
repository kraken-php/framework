<?php

namespace Kraken\Stream\Buffer;

use Kraken\Event\BaseEventEmitter;
use Kraken\Exception\Io\WriteException;
use Kraken\Loop\LoopInterface;
use Kraken\Stream\WritableStreamInterface;

/**
 * @override
 *
 * @event finish
 */
class MemorySoftBuffer extends BaseEventEmitter implements BufferInterface
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var bool
     */
    protected $listening;

    /**
     * @var int
     */
    protected $bufferSize;

    /**
     * @var bool
     */
    protected $writable;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var string
     */
    protected $text;

    /**
     * @param resource $resource
     * @param LoopInterface $loop
     */
    public function __construct($resource, LoopInterface $loop)
    {
        $this->resource = $resource;
        $this->loop = $loop;
        $this->listening = false;
        $this->bufferSize = 4096;
        $this->writable = true;
        $this->text = '';
    }

    /**
     *
     */
    public function __destruct()
    {
        parent::__destruct();

        unset($this->resource);
        unset($this->loop);
        unset($this->listening);
        unset($this->bufferSize);
        unset($this->writable);
        unset($this->text);
    }

    /**
     * @param int $bufferSize
     */
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * @return int BufferSize
     */
    public function getBufferSize()
    {
        return $this->bufferSize;
    }

    /**
     * @param string $text
     * @return bool
     * @throws WriteException
     */
    public function write($text)
    {
        if (!$this->writable)
        {
            throw new WriteException('Buffer is no longer writable.');
        }

        $this->text .= $text;

        if (!$this->listening)
        {
            $this->listening = true;
            $this->loop->addWriteStream($this->resource, function() {
                $this->handleWrite();
            });
        }

        return strlen($this->text) < $this->bufferSize;
    }

    /**
     * Close the Buffer and prevent any further writes.
     */
    public function close()
    {
        $this->writable = false;
        $this->listening = false;
        $this->text = '';

        $this->emit('close');
    }

    /**
     * Handle the outcoming stream.
     */
    protected function handleWrite()
    {
        $sent = fwrite($this->resource, $this->text);

        if ($sent === false)
        {
            $this->emit('error', [ new WriteException('Error occurred while writing to the stream resource.') ]);
            return;
        }

        $lenBefore = strlen($this->text);
        $lenAfter = $lenBefore - $sent;
        $this->text = (string) substr($this->text, $sent);

        if ($lenBefore >= $this->bufferSize && $lenAfter < $this->bufferSize)
        {
            $this->emit('drain');
        }

        if ($lenAfter === 0)
        {
            $this->loop->removeWriteStream($this->resource);
            $this->listening = false;
            $this->emit('finish');
        }
    }
}
