<?php

namespace Kraken\Runtime\Container;

use Dazzle\Promise\PromiseInterface;
use Kraken\Runtime\Runtime;

interface ProcessManagerInterface extends AbstractManagerInterface
{
    /**
     * Check if process container exists.
     *
     * @param string $alias
     * @return bool
     */
    public function existsProcess($alias);

    /**
     * Create a new process container.
     *
     * Flags might be one of:
     * Runtime::CREATE_DEFAULT - creates a new process only if it does not already exist
     * Runtime::CREATE_FORCE_SOFT - does the same as CREATE_DEFAULT, but in case of existing process tries to replace
     * it. Replacement is done by destroying existing process by sending shutdown message.
     * Runtime::CREATE_FORCE_HARD - does the same as CREATE_DEFAULT, but in case of existing process tries to replace
     * it. Replacement is done by forcefully destroying existing process.
     * Runtime::CREATE_FORCE - creates a new process if it does not exist or tries to replace existing firstly trying
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
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT, $params = []);

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
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT, $params = []);

    /**
     * Start existing process container.
     *
     * @param string $alias
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function startProcess($alias, $params = []);

    /**
     * Stop existing process container.
     *
     * @param string $alias
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function stopProcess($alias, $params = []);

    /**
     * Create multiple process containers at once.
     *
     * @see ProcessManagerInterface::createProcesses
     *
     * @param string[][] $definitions
     * @param int $flags
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT, $params = []);

    /**
     * Destroy multiple process containers at once.
     *
     * @see ProcessManagerInterface::destroyProcesses
     *
     * @param string[] $aliases
     * @param int $flags
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT, $params = []);

    /**
     * Start multiple process containers at once.
     *
     * @param $aliases
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function startProcesses($aliases, $params = []);

    /**
     * Stop multiple process containers at once.
     *
     * @param $aliases
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function stopProcesses($aliases, $params = []);

    /**
     * Get list of existing process containers.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function getProcesses();

    /**
     * Flush processes without destroying them.
     *
     * @param int $flags
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function flushProcesses($flags = Runtime::DESTROY_KEEP);
}
