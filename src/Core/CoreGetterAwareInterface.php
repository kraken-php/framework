<?php

namespace Kraken\Core;

interface CoreGetterAwareInterface
{
    /**
     * @return CoreInterface
     */
    public function getCore();

    /**
     * @return CoreInterface
     */
    public function core();
}
