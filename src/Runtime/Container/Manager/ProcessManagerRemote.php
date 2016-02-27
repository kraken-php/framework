<?php

namespace Kraken\Runtime\Container\Manager;

use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeInterface;

class ProcessManagerRemote implements ProcessManagerInterface
{
    /**
     * @var string
     */
    protected $runtime;

    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var string
     */
    protected $receiver;

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelBaseInterface $channel
     * @param string|null $receiver
     */
    public function __construct(RuntimeInterface $runtime, ChannelBaseInterface $channel, $receiver = null)
    {
        $this->runtime = $runtime;
        $this->channel = $channel;
        $this->receiver = ($receiver !== null) ? $receiver : $runtime->parent();
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
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:create', [ 'alias' => $alias, 'name' => $name, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:destroy', [ 'alias' => $alias, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startProcess($alias)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:start', [ 'alias' => $alias ])
        );

        return $req->call();
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopProcess($alias)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:stop', [ 'alias' => $alias ])
        );

        return $req->call();
    }

    /**
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:create', [ 'definitions' => $definitions, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:destroy', [ 'aliases' => $aliases, 'flags' => $flags ])
        );

        return $req->call();
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function startProcesses($aliases)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:start', [ 'aliases' => $aliases ])
        );

        return $req->call();
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopProcesses($aliases)
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('processes:stop', [ 'aliases' => $aliases ])
        );

        return $req->call();
    }

    /**
     * @return PromiseInterface
     */
    public function getProcesses()
    {
        $req = new Request(
            $this->channel,
            $this->receiver,
            new RuntimeCommand('process:get')
        );

        return $req->call();
    }

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushProcesses($flags = Runtime::DESTROY_KEEP)
    {
        return Promise::doReject(new RejectionException('Processes storage cannot be flushed.'));
    }
}