<?php

namespace Kraken\_Unit\Config\_Mock;

use Kraken\Config\ConfigAwareInterface;
use Kraken\Config\ConfigAwareTrait;
use Kraken\Config\ConfigInterface;

class ConfigAwareObject implements ConfigAwareInterface
{
    use ConfigAwareTrait;

    /**
     * @param ConfigInterface|null $config
     */
    public function __construct(ConfigInterface $config = null)
    {
        $this->config = $config;
    }
}
