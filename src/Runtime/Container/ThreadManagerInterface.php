<?php

namespace Kraken\Runtime\Container;

use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Runtime;

interface ThreadManagerInterface
{
    /**
     * Check if thread container exists.
     *
     * @param string $alias
     * @return bool
     */
    public function existsThread($alias);

    /**
     * Create a new thread container.
     *
     * Flags might be one of:
     * Runtime::CREATE_DEFAULT - creates a new thread only if it does not already exist
     * Runtime::CREATE_FORCE_SOFT - does the same as CREATE_DEFAULT, but in case of existing thread tries to replace
     * it. Replacement is done by destroying existing thread by sending shutdown message.
     * Runtime::CREATE_FORCE_HARD - does the same as CREATE_DEFAULT, but in case of existing thread tries to replace
     * it. Replacement is done by forcefully destroying existing thread.
     * Runtime::CREATE_FORCE - creates a new thread if it does not exist or tries to replace existing firstly trying
     * to destroy it gracefully, but it fails doing it forcefully.
     *
     * @param string $alias
     * @param string $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT);

    /**
     * Destroy existing container.
     *
     * Flags might be one of:
     * Runtime::DESTROY_KEEP - sets manager to not destroy runtime
     * Runtime::DESTROY_FORCE_SOFT - destroys runtime by sending message to shutdown
     * Runtime::DESTROY_FORCE_HARD - destroys runtime forcefully and immediately
     * Runtime::DESTROY_FORCE - first, tries to gracefully destroy runtime by sending message to shutdown, if it does
     * not receive answer, then closes it forcefully.
     *
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT);

    /**
     * Start existing thread container.
     *
     * @param string $alias
     * @return PromiseInterface
     */
    public function startThread($alias);

    /**
     * Stop existing thread container.
     *
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopThread($alias);

    /**
     * Create multiple thread containers at once.
     *
     * @see RuntimeManagerInterface::createThreads
     *
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT);

    /**
     * Destroy multiple thread containers at once.
     *
     * @see ThreadManagerInterface::destroyThreads
     *
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT);

    /**
     * Start multiple thread container at once.
     *
     * @param $aliases
     * @return PromiseInterface
     */
    public function startThreads($aliases);

    /**
     * Stop multiple thread container at once.
     *
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopThreads($aliases);

    /**
     * Get list of existing thread containers.
     *
     * @return PromiseInterface
     */
    public function getThreads();

    /**
     * Flush threads without destroying them.
     *
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP);
}
