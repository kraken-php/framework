<?php

namespace Kraken\Core;

interface CoreSetterAwareInterface
{
    /**
     * Set Core of which object is aware of or delete it by setting it to null.
     *
     * @param CoreInterface|null $core
     */
    public function setCore(CoreInterface $core = null);
}
