<?php

namespace Kraken\Runtime;

use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\Container\ThreadManagerInterface;

interface RuntimeManagerInterface extends ProcessManagerInterface, ThreadManagerInterface
{
    /**
     * @param string $alias
     * @return bool
     */
    public function existsRuntime($alias);

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyRuntime($alias, $flags = Runtime::DESTROY_FORCE_SOFT);

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startRuntime($alias);

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopRuntime($alias);

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyRuntimes($aliases, $flags = Runtime::DESTROY_FORCE_SOFT);

    /**
     * @param string[] $aliases
     * @return PromiseInterface
     */
    public function startRuntimes($aliases);

    /**
     * @param string[] $aliases
     * @return PromiseInterface
     */
    public function stopRuntimes($aliases);

    /**
     * @return PromiseInterface
     */
    public function getRuntimes();

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushRuntimes($flags = Runtime::DESTROY_KEEP);
}
