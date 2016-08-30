<?php

namespace Kraken\Config;

interface ConfigAwareInterface
{
    /**
     * Set Config of which object is aware of or unset by setting null.
     *
     * @param ConfigInterface|null $config
     */
    public function setConfig(ConfigInterface $config = null);

    /**
     * Get Config of which object is aware of.
     *
     * @return ConfigInterface|null
     */
    public function getConfig();
}
