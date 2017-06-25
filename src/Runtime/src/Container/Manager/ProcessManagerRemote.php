<?php

namespace Kraken\Runtime\Container\Manager;

use Dazzle\Channel\Channel;
use Dazzle\Promise\Promise;
use Dazzle\Channel\ChannelInterface;
use Dazzle\Channel\Extra\Request;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeContainerInterface;
use Dazzle\Throwable\Exception\Runtime\RejectionException;

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
    public function sendCommand($alias, $command, $params = [])
    {
        $req = new Request(
            $this->channel,
            $alias,
            new RuntimeCommand($command, $params)
        );

        return $req->call();
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
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:create', array_merge(
                $params,
                [ 'alias' => $alias, 'name' => $name, 'flags' => $flags ]
            ))
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:destroy', array_merge(
                $params,
                [ 'alias' => $alias, 'flags' => $flags ]
            ))
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProcess($alias, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:start', array_merge(
                $params,
                [ 'alias' => $alias ]
            ))
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProcess($alias, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:stop', array_merge(
                $params,
                [ 'alias' => $alias ]
            ))
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:create', array_merge(
                $params,
                [ 'definitions' => $definitions, 'flags' => $flags ])
            )
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:destroy', array_merge(
                $params,
                [ 'aliases' => $aliases, 'flags' => $flags ]
            ))
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProcesses($aliases, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:start', array_merge(
                $params,
                [ 'aliases' => $aliases ]
            ))
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProcesses($aliases, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:stop', array_merge(
                $params,
                [ 'aliases' => $aliases ]
            ))
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