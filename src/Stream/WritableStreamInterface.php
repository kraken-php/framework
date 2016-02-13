<?php

namespace Kraken\Stream;

use Kraken\Event\EventEmitterInterface;

/**
 * @override
 *
 * @event drain
 */
interface WritableStreamInterface extends EventEmitterInterface, StreamBasicInterface
{
    /**
     * Check if stream is writable.
     *
     * @return bool
     */
    public function isWritable();

    /**
     * Write
     *
     * @param string $text
     * @return bool
     */
    public function write($text);
}
