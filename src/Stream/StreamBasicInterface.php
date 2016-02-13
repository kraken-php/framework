<?php

namespace Kraken\Stream;

/**
 * @event error<Exception>
 * @event close
 */
interface StreamBasicInterface
{
    /**
     * Return the wrapped resource.
     *
     * @return resource
     */
    public function getResource();

    /**
     * Return array containg metadata of stream.
     *
     * @return string[]
     */
    public function getMetadata();

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
     * Check if stream is open.
     *
     * @return bool
     */
    public function isOpen();

    /**
     * Close Stream and underlying resource object.
     */
    public function close();
}
