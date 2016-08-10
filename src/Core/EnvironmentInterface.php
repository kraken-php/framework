<?php

namespace Kraken\Core;

interface EnvironmentInterface
{
    /**
     * Set PHP ini.settings of key=$key to value=$val.
     *
     * @param string $key
     * @param string $val
     * @return string|bool
     */
    public function setOption($key, $val);

    /**
     * Return PHP ini.setting for key=$key.
     *
     * @param string $key
     * @return string|bool
     */
    public function getOption($key);

    /**
     * Restore PHP ini.setting of key=$key to its default value.
     *
     * @param string $key
     * @return string|bool
     */
    public function restoreOption($key);

    /**
     * Return Framework env. value for setting of key=$key.
     *
     * @param string $key
     * @return string
     */
    public function getEnv($key);

    /**
     * Check if Framework env. value of key=$key equals $val.
     *
     * @param string $key
     * @param string $val
     * @return bool
     */
    public function matchEnv($key, $val);

    /**
     * Register Error Handler.
     *
     * @param callable $handler
     */
    public function registerErrorHandler(callable $handler);

    /**
     * Register Shutdown Handler.
     *
     * @param callable $handler
     */
    public function registerShutdownHandler(callable $handler);

    /**
     * Register Exception Handler.
     *
     * @param callable $handler
     */
    public function registerExceptionHandler(callable $handler);

    /**
     * Register Termination Handler.
     *
     * @param callable $handler
     */
    public function registerTerminationHandler(callable $handler);
}
