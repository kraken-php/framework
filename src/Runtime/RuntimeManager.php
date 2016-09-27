<?php

namespace Kraken\Runtime;

use Kraken\Channel\Channel;
use Kraken\Channel\Extra\Request;
use Kraken\Channel\ChannelInterface;
use Kraken\Promise\Promise;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\Container\ThreadManagerInterface;

class RuntimeManager implements RuntimeManagerInterface
{
    /**
     * @var ChannelInterface
     */
    protected $runtimeChannel;

    /**
     * @var ProcessManagerInterface
     */
    protected $processManager;

    /**
     * @var ThreadManagerInterface
     */
    protected $threadManager;

    /**
     * @param ChannelInterface $channel
     * @param ProcessManagerInterface $processManager
     * @param ThreadManagerInterface $threadManager
     */
    public function __construct(ChannelInterface $channel, ProcessManagerInterface $processManager, ThreadManagerInterface $threadManager)
    {
        $this->runtimeChannel = $channel;
        $this->processManager = $processManager;
        $this->threadManager  = $threadManager;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtimeChannel);
        unset($this->processManager);
        unset($this->threadManager);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function sendRequest($alias, $message, $params = [])
    {
        $req = new Request(
            $this->runtimeChannel,
            $alias,
            $message,
            $params
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function sendMessage($alias, $message, $flags = Channel::MODE_DEFAULT)
    {
        $result = $this->runtimeChannel->send(
            $alias,
            $message,
            $flags
        );

        return Promise::doResolve($result);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function sendCommand($alias, $command, $params = [])
    {
        $req = new Request(
            $this->runtimeChannel,
            $alias,
            new RuntimeCommand($command, $params)
        );

        return $req->call();
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
    public function destroyRuntime($alias, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        if ($this->existsThread($alias))
        {
            return $this->threadManager->destroyThread($alias, $flags, $params);
        }
        else if ($this->existsProcess($alias))
        {
            return $this->processManager->destroyProcess($alias, $flags, $params);
        }

        return Promise::doReject(new ResourceUndefinedException("Runtime with alias [$alias] does not exist."));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startRuntime($alias, $params = [])
    {
        if ($this->existsThread($alias))
        {
            return $this->threadManager->startThread($alias, $params);
        }
        else if ($this->existsProcess($alias))
        {
            return $this->processManager->startProcess($alias, $params);
        }

        return Promise::doReject(new ResourceUndefinedException("Runtime with alias [$alias] does not exist."));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopRuntime($alias, $params = [])
    {
        if ($this->existsThread($alias))
        {
            return $this->threadManager->stopThread($alias, $params);
        }
        else if ($this->existsProcess($alias))
        {
            return $this->processManager->stopProcess($alias, $params);
        }

        return Promise::doReject(new ResourceUndefinedException("Runtime with alias [$alias] does not exist."));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyRuntimes($aliases, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->destroyRuntime($alias, $flags, $params);
        }

        return Promise::all($promises);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startRuntimes($aliases, $params = [])
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->startRuntime($alias, $params);
        }

        return Promise::all($promises);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopRuntimes($aliases, $params = [])
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->stopRuntime($alias, $params);
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
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        return $this->processManager->createProcess($alias, $name, $flags, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        return $this->processManager->destroyProcess($alias, $flags, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProcess($alias, $params = [])
    {
        return $this->processManager->startProcess($alias, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProcess($alias, $params = [])
    {
        return $this->processManager->stopProcess($alias, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        return $this->processManager->createProcesses($definitions, $flags, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        return $this->processManager->destroyProcesses($aliases, $flags, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProcesses($aliases, $params = [])
    {
        return $this->processManager->startProcesses($aliases, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProcesses($aliases, $params = [])
    {
        return $this->processManager->stopProcesses($aliases, $params);
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
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        return $this->threadManager->createThread($alias, $name, $flags, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        return $this->threadManager->destroyThread($alias, $flags, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThread($alias, $params = [])
    {
        return $this->threadManager->startThread($alias, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThread($alias, $params = [])
    {
        return $this->threadManager->stopThread($alias, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        return $this->threadManager->createThreads($definitions, $flags, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        return $this->threadManager->destroyThreads($aliases, $flags, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThreads($aliases, $params = [])
    {
        return $this->threadManager->startThreads($aliases, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThreads($aliases, $params = [])
    {
        return $this->threadManager->stopThreads($aliases, $params);
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
