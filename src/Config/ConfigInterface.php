<?php

namespace Kraken\Config;

interface ConfigInterface extends ConfigReaderInterface, ConfigWriterInterface
{
    /**
     * Set internal array configuration to $config.
     *
     * @param array $config
     */
    public function setConfiguration($config);

    /**
     * Get internal array configuration.
     *
     * @return array
     */
    public function getConfiguration();

    /**
     * Set overwrite handler used while merging configurations.
     *
     * @param callable|null $handler
     */
    public function setOverwriteHandler(callable $handler = null);

    /**
     * Get currently set overwrite handler for merging configurations.
     *
     * @return callable $handler
     */
    public function getOverwriteHandler();
}
