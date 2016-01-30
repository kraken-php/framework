<?php

namespace Kraken\Core;

interface EnvironmentInterface
{
    /**
     * @param string $key
     * @param string $value
     * @return string|bool
     */
    public function setOption($key, $value);

    /**
     * @param string $key
     * @return string|bool
     */
    public function getOption($key);

    /**
     * @param string $key
     * @return string|bool
     */
    public function restoreOption($key);

    /**
     * @param string $key
     * @return string
     */
    public function getEnv($key);

    /**
     * @param string $key
     * @param string $val
     * @return bool
     */
    public function matchEnv($env, $val);

    /**
     * @param callable $handler
     */
    public function registerErrorHandler(callable $handler);

    /**
     * @param callable $handler
     */
    public function registerShutdownHandler(callable $handler);

    /**
     * @param callable $handler
     */
    public function registerExceptionHandler(callable $handler);
}
