<?php

namespace Kraken\Stream;

/**
 * @event error : callable(object, Error|Exception)
 * @event close : callable(object)
 * @event done  : callable(object)
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
     * Return file descriptor of wrapped resource.
     *
     * @return int
     */
    public function getResourceId();

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
