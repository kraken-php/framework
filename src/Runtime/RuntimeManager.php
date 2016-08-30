<?php

namespace Kraken\Runtime;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\Container\ThreadManagerInterface;

class RuntimeManager implements RuntimeManagerInterface
{
    /**
     * @var ProcessManagerInterface
     */
    protected $processManager;

    /**
     * @var ThreadManagerInterface
     */
    protected $threadManager;

    /**
     * @param ProcessManagerInterface $processManager
     * @param ThreadManagerInterface $threadManager
     */
    public function __construct(ProcessManagerInterface $processManager, ThreadManagerInterface $threadManager)
    {
        $this->processManager = $processManager;
        $this->threadManager = $threadManager;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->processManager);
        unset($this->threadManager);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsRuntime($alias)
    {
        return $this->existsProcess($alias) || $this->existsThread($alias);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyRuntime($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        if ($this->existsThread($alias))
        {
            return $this->threadManager->destroyThread($alias, $flags);
        }
        else if ($this->existsProcess($alias))
        {
            return $this->processManager->destroyProcess($alias, $flags);
        }

        return Promise::doReject(new ResourceUndefinedException("Runtime with alias [$alias] does not exist."));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startRuntime($alias)
    {
        if ($this->existsThread($alias))
        {
            return $this->threadManager->startThread($alias);
        }
        else if ($this->existsProcess($alias))
        {
            return $this->processManager->startProcess($alias);
        }

        return Promise::doReject(new ResourceUndefinedException("Runtime with alias [$alias] does not exist."));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopRuntime($alias)
    {
        if ($this->existsThread($alias))
        {
            return $this->threadManager->stopThread($alias);
        }
        else if ($this->existsProcess($alias))
        {
            return $this->processManager->stopProcess($alias);
        }

        return Promise::doReject(new ResourceUndefinedException("Runtime with alias [$alias] does not exist."));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyRuntimes($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->destroyRuntime($alias, $flags);
        }

        return Promise::all($promises);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startRuntimes($aliases)
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->startRuntime($alias);
        }

        return Promise::all($promises);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopRuntimes($aliases)
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->stopRuntime($alias);
        }

        return Promise::all($promises);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getRuntimes()
    {
        return Promise::reduce(
            [
                $this->getThreads(),
                $this->getProcesses()
            ],
            function($carry, $item) {
                return array_unique(array_merge($carry, $item));
            },
            []
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function flushRuntimes($flags = Runtime::DESTROY_KEEP)
    {
        return Promise::all([
            $this->flushThreads($flags),
            $this->flushProcesses($flags)
        ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsProcess($alias)
    {
        return $this->processManager->existsProcess($alias);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        return $this->processManager->createProcess($alias, $name, $flags);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return $this->processManager->destroyProcess($alias, $flags);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProcess($alias)
    {
        return $this->processManager->startProcess($alias);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProcess($alias)
    {
        return $this->processManager->stopProcess($alias);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        return $this->processManager->createProcesses($definitions, $flags);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return $this->processManager->destroyProcesses($aliases, $flags);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProcesses($aliases)
    {
        return $this->processManager->startProcesses($aliases);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProcesses($aliases)
    {
        return $this->processManager->stopProcesses($aliases);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getProcesses()
    {
        return $this->processManager->getProcesses();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function flushProcesses($flags = Runtime::DESTROY_KEEP)
    {
        return $this->processManager->flushProcesses($flags);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsThread($alias)
    {
        return $this->threadManager->existsThread($alias);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        return $this->threadManager->createThread($alias, $name, $flags);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return $this->threadManager->destroyThread($alias, $flags);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThread($alias)
    {
        return $this->threadManager->startThread($alias);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThread($alias)
    {
        return $this->threadManager->stopThread($alias);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        return $this->threadManager->createThreads($definitions, $flags);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return $this->threadManager->destroyThreads($aliases, $flags);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThreads($aliases)
    {
        return $this->threadManager->startThreads($aliases);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThreads($aliases)
    {
        return $this->threadManager->stopThreads($aliases);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getThreads()
    {
        return $this->threadManager->getThreads();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP)
    {
        return $this->threadManager->flushThreads($flags);
    }
}
