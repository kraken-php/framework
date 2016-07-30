<?php

namespace Kraken\Config;

trait ConfigAwareTrait
{
    /**
     * @var ConfigInterface|null
     */
    protected $config = null;

    /**
     * @see ConfigAwareInterface::setConfig
     */
    public function setConfig(ConfigInterface $config = null)
    {
        $this->config = $config;
    }

    /**
     * @see ConfigAwareInterface::getConfig
     */
    public function getConfig()
    {
        return $this->config;
    }
}
