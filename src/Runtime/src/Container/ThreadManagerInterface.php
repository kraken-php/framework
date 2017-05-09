<?php

namespace Kraken\Runtime\Container;

use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Runtime;

interface ThreadManagerInterface extends AbstractManagerInterface
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
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT, $params = []);

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
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT, $params = []);

    /**
     * Start existing thread container.
     *
     * @param string $alias
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function startThread($alias, $params = []);

    /**
     * Stop existing thread container.
     *
     * @param string $alias
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function stopThread($alias, $params = []);

    /**
     * Create multiple thread containers at once.
     *
     * @see RuntimeManagerInterface::createThreads
     *
     * @param string[][] $definitions
     * @param int $flags
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT, $params = []);

    /**
     * Destroy multiple thread containers at once.
     *
     * @see ThreadManagerInterface::destroyThreads
     *
     * @param string[] $aliases
     * @param int $flags
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT, $params = []);

    /**
     * Start multiple thread container at once.
     *
     * @param string[] $aliases
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function startThreads($aliases, $params = []);

    /**
     * Stop multiple thread container at once.
     *
     * @param string[] $aliases
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function stopThreads($aliases, $params = []);

    /**
     * Get list of existing thread containers.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function getThreads();

    /**
     * Flush threads without destroying them.
     *
     * @param int $flags
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP);
}
