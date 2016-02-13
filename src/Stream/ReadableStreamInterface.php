<?php

namespace Kraken\Stream;

use Kraken\Event\EventEmitterInterface;

/**
 * @override
 *
 * @event data<string>
 */
interface ReadableStreamInterface extends EventEmitterInterface, StreamBasicInterface
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

//    /**
//     * Pause incoming data and all events.
//     *
//     * @return mixed
//     */
//    public function pause();
//
//    /**
//     * Resume incoming data and all events.
//     *
//     * @return mixed
//     */
//    public function resume();

//    public function pipe(WritableStreamInterface $dest, array $options = array());
}
