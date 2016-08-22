<?php

namespace Kraken\Runtime\Container\Manager;

use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\Container\ThreadManagerInterface;

class ThreadManagerNull implements ThreadManagerInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function existsThread($alias)
    {
        return false;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        return Promise::doReject(
            new RejectionException("Thread [$alias] could not be created.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return Promise::doResolve(
            "Thread [$alias] was not needed to be destroyed, because it had not existed."
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThread($alias)
    {
        return Promise::doReject(
            new RejectionException("Runtime [$alias] could not be started.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThread($alias)
    {
        return Promise::doReject(
            new RejectionException("Runtime [$alias] could not be stopped.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        return Promise::doReject(
            new RejectionException("Threads could not be created.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return Promise::doResolve(
            "Threads have been destroyed."
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThreads($aliases)
    {
        return Promise::doReject(
            new RejectionException("Threads could not be started.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThreads($aliases)
    {
        return Promise::doReject(
            new RejectionException("Threads could not be stopped.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getThreads()
    {
        return Promise::doResolve([]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP)
    {
        return Promise::doResolve(
            "Threads have been flushed."
        );
    }
}
