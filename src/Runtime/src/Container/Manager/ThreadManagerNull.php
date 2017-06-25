<?php

namespace Kraken\Runtime\Container\Manager;

use Kraken\Channel\Channel;
use Kraken\Promise\Promise;
use Kraken\Runtime\Container\ThreadManagerInterface;
use Kraken\Runtime\Runtime;
use Dazzle\Throwable\Exception\Runtime\RejectionException;

class ThreadManagerNull implements ThreadManagerInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function sendRequest($alias, $message, $params = [])
    {
        return Promise::doReject(
            new RejectionException("Request for [$alias] cannot be send on null manager.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function sendMessage($alias, $message, $flags = Channel::MODE_DEFAULT)
    {
        return Promise::doReject(
            new RejectionException("Message for [$alias] cannot be send on null manager.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function sendCommand($alias, $command, $params = [])
    {
        return Promise::doReject(
            new RejectionException("Command to [$alias] cannot be send on null manager.")
        );
    }

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
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        return Promise::doReject(
            new RejectionException("Thread [$alias] could not be created.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        return Promise::doResolve(
            "Thread [$alias] was not needed to be destroyed, because it had not existed."
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThread($alias, $params = [])
    {
        return Promise::doReject(
            new RejectionException("Runtime [$alias] could not be started.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThread($alias, $params = [])
    {
        return Promise::doReject(
            new RejectionException("Runtime [$alias] could not be stopped.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        return Promise::doReject(
            new RejectionException("Threads could not be created.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        return Promise::doResolve(
            "Threads have been destroyed."
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThreads($aliases, $params = [])
    {
        return Promise::doReject(
            new RejectionException("Threads could not be started.")
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThreads($aliases, $params = [])
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
