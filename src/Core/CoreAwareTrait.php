<?php

namespace Kraken\Core;

trait CoreAwareTrait
{
    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @param CoreInterface|null $core
     */
    public function setCore(CoreInterface $core = null)
    {
        $this->core = $core;
    }

    /**
     * @return CoreInterface
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @return CoreInterface
     */
    public function core()
    {
        return $this->core;
    }
}
