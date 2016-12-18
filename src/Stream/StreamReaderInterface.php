<?php

namespace Kraken\Stream;

use Kraken\Event\EventEmitterInterface;

/**
 * @event data : callable(object, string)
 * @event end  : callable(object)
 */
interface StreamReaderInterface extends EventEmitterInterface, StreamSeekerInterface
{
    /**
     * Check if stream is readable.
     *
     * @return bool
     */
    public function isReadable();

    /**
     * Read the contents of the string.
     *
     * Read the contents of the string. $length specifies maximum number of bytes read. If it is not set whole contents
     * will be read.
     *
     * @param int|null $length
     * @return string
     */
    public function read($length = null);

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
}
