<?php

namespace Kraken\Runtime;

interface RuntimeContextInterface
{
    /**
     * Return type of RuntimeContainer.
     *
     * This method returns one of: Runtime::UNIT_PROCESS, Runtime::UNIT_THREAD, Runtime::UNIT_UNDEFINED.
     *
     * @return string
     */
    public function getType();

    /**
     * Return parent alias or null if current RuntimeContainer is root.
     *
     * @return string|null
     */
    public function getParent();

    /**
     * Return alias of current container.
     *
     * @return string
     */
    public function getAlias();

    /**
     * Return name or class of current container.
     *
     * @return string
     */
    public function getName();
}
