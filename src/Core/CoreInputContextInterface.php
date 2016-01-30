<?php

namespace Kraken\Core;

interface CoreInputContextInterface
{
    /**
     * @return string
     */
    public function type();

    /**
     * @return string|null
     */
    public function parent();

    /**
     * @return string
     */
    public function alias();

    /**
     * @return string
     */
    public function name();
}
