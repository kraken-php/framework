<?php

namespace Kraken\Stream;

/**
 * @event error<Exception>
 * @event close
 */
interface StreamBaseInterface
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
     * Return type of stream.
     *
     * @return string
     */
    public function getStreamType();

    /**
     * Return type of stream wrapper.
     *
     * @return string
     */
    public function getWrapperType();

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
