<?php

namespace Kraken\Runtime;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;
use Kraken\Runtime\Process\ProcessManagerInterface;
use Kraken\Runtime\Thread\ThreadManagerInterface;

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
     * @param string $alias
     * @return bool
     */
    public function existsRuntime($alias)
    {
        return $this->existsProcess($alias) || $this->existsThread($alias);
    }

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
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
     * @param string $alias
     * @return PromiseInterface
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
     * @param string $alias
     * @return PromiseInterface
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
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
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
     * @param string[] $aliases
     * @return PromiseInterface
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
     * @param string[] $aliases
     * @return PromiseInterface
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
     * @return PromiseInterface
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
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushRuntimes($flags = Runtime::DESTROY_KEEP)
    {
        return Promise::all([
            $this->flushThreads($flags),
            $this->flushProcesses($flags)
        ]);
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function existsProcess($alias)
    {
        return $this->processManager->existsProcess($alias);
    }

    /**
     * @param string $alias
     * @param string|null $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        return $this->processManager->createProcess($alias, $name, $flags);
    }

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return $this->processManager->destroyProcess($alias, $flags);
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startProcess($alias)
    {
        return $this->processManager->startProcess($alias);
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopProcess($alias)
    {
        return $this->processManager->stopProcess($alias);
    }

    /**
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        return $this->processManager->createProcesses($definitions, $flags);
    }

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return $this->processManager->destroyProcesses($aliases, $flags);
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function startProcesses($aliases)
    {
        return $this->processManager->startProcesses($aliases);
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopProcesses($aliases)
    {
        return $this->processManager->stopProcesses($aliases);
    }

    /**
     * @return PromiseInterface
     */
    public function getProcesses()
    {
        return $this->processManager->getProcesses();
    }

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushProcesses($flags = Runtime::DESTROY_KEEP)
    {
        return $this->processManager->flushProcesses($flags);
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function existsThread($alias)
    {
        return $this->threadManager->existsThread($alias);
    }

    /**
     * @param string $alias
     * @param string|null $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        return $this->threadManager->createThread($alias, $name, $flags);
    }

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return $this->threadManager->destroyThread($alias, $flags);
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startThread($alias)
    {
        return $this->threadManager->startThread($alias);
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopThread($alias)
    {
        return $this->threadManager->stopThread($alias);
    }

    /**
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        return $this->threadManager->createThreads($definitions, $flags);
    }

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        return $this->threadManager->destroyThreads($aliases, $flags);
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function startThreads($aliases)
    {
        return $this->threadManager->startThreads($aliases);
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopThreads($aliases)
    {
        return $this->threadManager->stopThreads($aliases);
    }


    /**
     * @return PromiseInterface
     */
    public function getThreads()
    {
        return $this->threadManager->getThreads();
    }

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP)
    {
        return $this->threadManager->flushThreads($flags);
    }
}
