<?php

namespace Kraken\Core;

interface CoreInputContextInterface
{
    /**
     * Return type of RuntimeContainer.
     *
     * This method returns one of: Runtime::UNIT_PROCESS, Runtime::UNIT_THREAD, Runtime::UNIT_UNDEFINED.
     *
     * @return string
     */
    public function type();

    /**
     * Return parent alias or null if current RuntimeContainer is root.
     *
     * @return string|null
     */
    public function parent();

    /**
     * Return alias of current container.
     *
     * @return string
     */
    public function alias();

    /**
     * Return name or class of current container.
     *
     * @return string
     */
    public function name();
}
