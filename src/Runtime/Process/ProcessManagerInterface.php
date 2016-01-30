<?php

namespace Kraken\Runtime\Process;

use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Runtime;

interface ProcessManagerInterface
{
    /**
     * @param string $alias
     * @return bool
     */
    public function existsProcess($alias);

    /**
     * @param string $alias
     * @param string $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT);

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT);

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startProcess($alias);

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopProcess($alias);

    /**
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT);

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT);

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function startProcesses($aliases);

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopProcesses($aliases);

    /**
     * @return PromiseInterface
     */
    public function getProcesses();

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushProcesses($flags = Runtime::DESTROY_KEEP);
}
