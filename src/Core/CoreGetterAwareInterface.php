<?php

namespace Kraken\Core;

interface CoreGetterAwareInterface
{
    /**
     * Get Core of which object is aware of or null if no object is set.
     *
     * @return CoreInterface|null
     */
    public function getCore();
}
