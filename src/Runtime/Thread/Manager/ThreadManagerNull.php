<?php

namespace Kraken\Runtime\Thread\Manager;

use Kraken\Exception\Runtime\RejectionException;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\Thread\ThreadManagerInterface;

class ThreadManagerNull implements ThreadManagerInterface
{
    /**
     * @param string $alias
     * @return bool
     */
    public function existsThread($alias)
    {
        return false;
    }

    /**
     * @param string $alias
     * @param string|null $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        return Promise::doReject(
            new RejectionException("Thread [$alias] could not be created.")
        );
    }

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return Promise::doResolve(
            "Thread [$alias] was not needed to be destroyed, because it had not existed."
        );
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startThread($alias)
    {
        return Promise::doReject(
            new RejectionException("Runtime [$alias] could not be started.")
        );
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopThread($alias)
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
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        return Promise::doReject(
            new RejectionException("Threads could not be created.")
        );
    }

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return Promise::doResolve(
            "Threads have been destroyed."
        );
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function startThreads($aliases)
    {
        return Promise::doReject(
            new RejectionException("Threads could not be started.")
        );
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopThreads($aliases)
    {
        return Promise::doReject(
            new RejectionException("Threads could not be stopped.")
        );
    }

    /**
     * @return PromiseInterface
     */
    public function getThreads()
    {
        return Promise::doResolve([]);
    }

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP)
    {
        return Promise::doResolve(
            "Threads have been flushed."
        );
    }
}