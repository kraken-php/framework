<?php

namespace Kraken\Stream;

use Kraken\Event\EventEmitterInterface;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Runtime\WriteException;

/**
 * @event seek : callable(object, int)
 */
interface StreamSeekerInterface extends EventEmitterInterface, StreamBaseInterface
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
