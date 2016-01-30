<?php

namespace Kraken\Runtime\Process\Manager;

use Kraken\Exception\Runtime\RejectionException;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Process\ProcessManagerInterface;
use Kraken\Runtime\Runtime;

class ProcessManagerNull implements ProcessManagerInterface
{
    /**
     * @param string $alias
     * @return bool
     */
    public function existsProcess($alias)
    {
        return false;
    }

    /**
     * @param string $alias
     * @param string|null $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        return Promise::doReject(
            new RejectionException("Process [$alias] could not be created.")
        );
    }

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return Promise::doResolve(
            "Process [$alias] was not needed to be destroyed, because it had not existed."
        );
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startProcess($alias)
    {
        return Promise::doReject(
            new RejectionException("Runtime [$alias] could not be started.")
        );
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopProcess($alias)
    {
        return Promise::doReject(
            new RejectionException("Runtime [$alias] could not be stopped.")
        );
    }

    /**
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        return Promise::doReject(
            new RejectionException("Processes could not be created.")
        );
    }

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return Promise::doResolve(
            "Processes have been destroyed."
        );
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function startProcesses($aliases)
    {
        return Promise::doReject(
            new RejectionException("Processes could not be started.")
        );
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopProcesses($aliases)
    {
        return Promise::doReject(
            new RejectionException("Processes could not be stopped.")
        );
    }

    /**
     * @return PromiseInterface
     */
    public function getProcesses()
    {
        return Promise::doResolve([]);
    }

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushProcesses($flags = Runtime::DESTROY_KEEP)
    {
        return Promise::doResolve(
            "Processes have been flushed."
        );
    }
}
