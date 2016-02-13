<?php

namespace Kraken\Stream;

use Kraken\Event\EventEmitterInterface;
use Kraken\Exception\Io\ReadException;
use Kraken\Exception\Io\WriteException;

/**
 * @event seek callable(int)
 */
interface SeekableStreamInterface extends EventEmitterInterface, StreamBasicInterface
{
    /**
     * Check if stream is seekable.
     *
     * @return bool
     */
    public function isSeekable();

    /**
     * Get the position of the file pointer.
     *
     * @return int
     * @throws ReadException
     */
    public function tell();

    /**
     * Move the file pointer to a new position.
     *
     * The new position, measured in bytes from the beginning of the file,
     * is obtained by adding $offset to the position specified by $whence.
     *
     * @param int $offset
     * @param int $whence
     * @throws WriteException
     */
    public function seek($offset, $whence = SEEK_SET);

    /**
     * Move the file pointer to the beginning of the stream.
     *
     * @throws WriteException
     */
    public function rewind();
}
