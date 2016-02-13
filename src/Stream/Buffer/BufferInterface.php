<?php

namespace Kraken\Stream\Buffer;

use Kraken\Event\EventEmitterInterface;
use Kraken\Exception\Io\WriteException;

/**
 * @event drain
 * @event full-drain
 * @event error(Exception)
 * @event close
 */
interface BufferInterface extends EventEmitterInterface
{
    /**
     * Set the size of stream buffer in bytes.
     *
     * @param int $bufferSize
     */
    public function setBufferSize($bufferSize);

    /**
     * Get the current size of stream buffer.
     *
     * @return int
     */
    public function getBufferSize();

    /**
     * Write additional data to buffer.
     *
     * @param string $text
     * @return bool
     * @throws WriteException
     */
    public function write($text);

    /**
     * Close the Buffer and prevent any further writes.
     */
    public function close();
}
