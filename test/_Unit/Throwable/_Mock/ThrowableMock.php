<?php

namespace Kraken\_Unit\Throwable\_Mock;

class ThrowableMock
{
    /**
     * Return proxied Throwable message.
     *
     * @return string
     */
    final public function getMessage()
    {
        return 'message';
    }

    /**
     * Return proxied Throwable code.
     *
     * @return int
     */
    final public function getCode()
    {
        return 5;
    }

    /**
     * Return proxied Throwable file.
     *
     * @return string
     */
    final public function getFile()
    {
        return 'file';
    }

    /**
     * Return proxied Throwable code.
     *
     * @return int
     */
    final public function getLine()
    {
        return 25;
    }

    /**
     * Return proxied Throwable trace.
     *
     * @return array
     */
    final public function getTrace()
    {
        return [];
    }

    /**
     * Return proxied Throwable previous element.
     *
     * @return \Error|\Exception|null
     */
    final public function getPrevious()
    {
        return null;
    }

    /**
     * Return proxied Throwable trace in string format.
     *
     * @return string
     */
    final public function getTraceAsString()
    {
        return 'trace';
    }
}
