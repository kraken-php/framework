<?php

namespace Kraken\Core;

interface CoreSetterAwareInterface
{
    /**
     * @param CoreInterface $core
     */
    public function setCore(CoreInterface $core);
}
