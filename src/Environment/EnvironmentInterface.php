<?php

namespace Kraken\Environment;

interface EnvironmentInterface
{
    /**
     * Set an environment variable.
     *
     * The environment variable value is stripped of single and double quotes.
     *
     * @param string $name
     * @param string|null $value
     */
    public function setEnv($name, $value);

    /**
     * Search the different places for environment variables and return first value found.
     *
     * @param string $name
     * @return string|null
     */
    public function getEnv($name);

    /**
     * Remove an environment variable.
     *
     * @param string $name
     */
    public function removeEnv($name);

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
    public function removeOption($key);

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
