<?php

namespace Kraken\Runtime\Container;

use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Runtime;

interface ThreadManagerInterface
{
    /**
     * @param string $alias
     * @return bool
     */
    public function existsThread($alias);

    /**
     * @param string $alias
     * @param string $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT);

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT);

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startThread($alias);

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopThread($alias);

    /**
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT);

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT);

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function startThreads($aliases);

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopThreads($aliases);

    /**
     * @return PromiseInterface
     */
    public function getThreads();

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP);
}
