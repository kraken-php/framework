<?php

namespace Kraken\Pattern\Buffer;

use Kraken\Exception\Io\ReadException;
use Kraken\Exception\Io\WriteException;

interface BufferInterface
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
     * Read all data from the buffer.
     *
     * @return string
     * @throws ReadException
     */
    public function read();

    /**
     * Flush given number of bytes from Buffer or flush it completely if $length is set to null.
     *
     * @param int|null $length
     */
    public function flush($length = null);

    /**
     * Close the Buffer and prevent any further writes.
     */
    public function close();
}
