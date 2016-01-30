<?php

namespace Kraken\Config;

trait ConfigAwareTrait
{
    /**
     * @var ConfigInterface|null
     */
    protected $config = null;

    /**
     * @param ConfigInterface|null $config
     */
    public function setConfig(ConfigInterface $config = null)
    {
        $this->config = $config;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return ConfigInterface
     */
    public function config()
    {
        return $this->config;
    }
}
