<?php

namespace Kraken\_Unit\Config;

use Kraken\_Unit\Config\_Mock\ConfigAwareObject;
use Kraken\Config\Config;
use Kraken\Config\ConfigAwareInterface;
use Kraken\Config\ConfigInterface;
use Kraken\Test\TUnit;

class ConfigAwareObjectTest extends TUnit
{
    /**
     *
     */
    public function testApiSetConfig_SetsConfig()
    {
        $config = $this->createConfig();
        $aware = $this->createConfigAwareObject();

        $this->assertSame(null, $aware->getConfig());
        $aware->setConfig($config);

        $this->assertSame($config, $aware->getConfig());
    }

    /**
     *
     */
    public function testApiSetConfig_RemovesConfig_WhenSetToNull()
    {
        $config = $this->createConfig();
        $aware = $this->createConfigAwareObject($config);

        $this->assertSame($config, $aware->getConfig());
        $aware->setConfig(null);

        $this->assertSame(null, $aware->getConfig());
    }

    /**
     *
     */
    public function testApiGetConfig_GetsConfig()
    {
        $config = $this->createConfig();
        $aware = $this->createConfigAwareObject($config);

        $this->assertSame($config, $aware->getConfig());
    }

    /**
     * @param ConfigInterface|null $config
     * @return ConfigAwareInterface
     */
    public function createConfigAwareObject(ConfigInterface $config = null)
    {
        return new ConfigAwareObject($config);
    }

    /**
     * @return Config
     */
    public function createConfig()
    {
        return new Config();
    }
}
