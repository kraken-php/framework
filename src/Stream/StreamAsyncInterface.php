<?php

namespace Kraken\Stream;

interface StreamAsyncInterface extends StreamInterface
{
    /**
     * Check if stream is paused.
     *
     * @return bool
     */
    public function isPaused();

    /**
     * Pause incoming data and all events.
     *
     * @return mixed
     */
    public function pause();

    /**
     * Resume incoming data and all events.
     *
     * @return mixed
     */
    public function resume();
}
