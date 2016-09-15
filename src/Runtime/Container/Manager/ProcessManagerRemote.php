<?php

namespace Kraken\Runtime\Container\Manager;

use Kraken\Channel\Channel;
use Kraken\Promise\Promise;
use Kraken\Channel\ChannelInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ProcessManagerRemote implements ProcessManagerInterface
{
    /**
     * @var string
     */
    protected $runtime;

    /**
     * @var ChannelInterface
     */
    protected $channel;

    /**
     * @var string
     */
    protected $receiver;

    /**
     * @param RuntimeContainerInterface $runtime
     * @param ChannelInterface $channel
     * @param string|null $receiver
     */
    public function __construct(RuntimeContainerInterface $runtime, ChannelInterface $channel, $receiver = null)
    {
        $this->runtime = $runtime;
        $this->channel = $channel;
        $this->receiver = $receiver !== null ? $receiver : $runtime->getParent();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
        unset($this->channel);
        unset($this->receiver);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function sendRequest($alias, $message, $params = [])
    {
        $req = new Request(
            $this->channel,
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
        $result = $this->channel->send(
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
    public function existsProcess($alias)
    {
        return false;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:create', [ 'alias' => $alias, 'name' => $name, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:destroy', [ 'alias' => $alias, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProcess($alias)
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:start', [ 'alias' => $alias ])
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProcess($alias)
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:stop', [ 'alias' => $alias ])
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:create', [ 'definitions' => $definitions, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:destroy', [ 'aliases' => $aliases, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProcesses($aliases)
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:start', [ 'aliases' => $aliases ])
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProcesses($aliases)
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:stop', [ 'aliases' => $aliases ])
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getProcesses()
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:get')
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function flushProcesses($flags = Runtime::DESTROY_KEEP)
    {
        return Promise::doReject(new RejectionException('Processes storage cannot be flushed.'));
    }

    /**
     * Create Request.
     *
     * @param ChannelInterface $channel
     * @param string $receiver
     * @param string $command
     * @return Request
     */
    protected function createRequest(ChannelInterface $channel, $receiver, $command)
    {
        return new Request($channel, $receiver, $command);
    }
}