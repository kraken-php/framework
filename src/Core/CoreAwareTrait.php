<?php

namespace Kraken\Core;

trait CoreAwareTrait
{
    /**
     * @var CoreInterface|null
     */
    protected $core = null;

    /**
     * @see CoreSetterAwareInterface::setCore
     */
    public function setCore(CoreInterface $core = null)
    {
        $this->core = $core;
    }

    /**
     * @see CoreGetterAwareInterface::getCore
     */
    public function getCore()
    {
        return $this->core;
    }
}
