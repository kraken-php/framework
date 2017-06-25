<?php

namespace Kraken\Runtime;

use Dazzle\Promise\PromiseInterface;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\Container\ThreadManagerInterface;

interface RuntimeManagerInterface extends ProcessManagerInterface, ThreadManagerInterface
{
    /**
     * Check if container exists.
     *
     * @param string $alias
     * @return bool
     */
    public function existsRuntime($alias);

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
    public function destroyRuntime($alias, $flags = Runtime::DESTROY_FORCE_SOFT, $params = []);

    /**
     * Start existing container.
     *
     * @param string $alias
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function startRuntime($alias, $params = []);

    /**
     * Stop existing container.
     *
     * @param string $alias
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function stopRuntime($alias, $params = []);

    /**
     * Destroy multiple containers at once.
     *
     * @see RuntimeManagerInterface::destroyRuntimes
     *
     * @param string[] $aliases
     * @param int $flags
     * @param mixed[]] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function destroyRuntimes($aliases, $flags = Runtime::DESTROY_FORCE_SOFT, $params = []);

    /**
     * Start multiple containers at once.
     *
     * @see RuntimeManagerInterface::startRuntimes
     *
     * @param string[] $aliases
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function startRuntimes($aliases, $params = []);

    /**
     * Stop multiple containers at once.
     *
     * @param string[] $aliases
     * @param string[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function stopRuntimes($aliases, $params = []);

    /**
     * Get list of existing containers.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function getRuntimes();

    /**
     * Flush runtimes without destroying them.
     *
     * @param int $flags
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function flushRuntimes($flags = Runtime::DESTROY_KEEP);
}
