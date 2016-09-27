<?php

namespace Kraken\Runtime\Container\Manager;

use Kraken\Channel\Channel;
use Kraken\Channel\Extra\Request;
use Kraken\Channel\ChannelInterface;
use Kraken\Promise\Promise;
use Kraken\Runtime\Container\Thread\ThreadController;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Runtime\Container\Thread\ThreadWrapper;
use Kraken\Runtime\Container\ThreadManagerInterface;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Logic\ResourceOccupiedException;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class ThreadManagerBase implements ThreadManagerInterface
{
    /**
     * @var RuntimeContainerInterface
     */
    protected $runtime;

    /**
     * @var ChannelInterface
     */
    protected $channel;

    /**
     * @var string[]
     */
    protected $context;

    /**
     * @var ThreadWrapper[]
     */
    protected $threads;

    /**
     * @param RuntimeContainerInterface $runtime
     * @param ChannelInterface $channel
     * @param string[] $context
     */
    public function __construct(RuntimeContainerInterface $runtime, ChannelInterface $channel, $context)
    {
        $this->runtime = $runtime;
        $this->channel = $channel;
        $this->context = $context;
        $this->threads = [];
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
        unset($this->channel);
        unset($this->context);
        unset($this->threads);
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
    public function existsThread($alias)
    {
        return isset($this->threads[$alias]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        if (isset($this->threads[$alias]))
        {
            if ($name === null)
            {
                $name = $this->threads[$alias]->name;
            }

            $manager = $this;

            if ($flags === Runtime::CREATE_FORCE_SOFT)
            {
                return $this
                    ->destroyThread($alias, Runtime::DESTROY_FORCE_SOFT, $params)
                    ->then(
                        function() use($manager, $alias, $name, $params) {
                            return $manager->createThread($alias, $name, $params);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE_HARD)
            {
                return $this
                    ->destroyThread($alias, Runtime::DESTROY_FORCE_HARD, $params)
                    ->then(
                        function() use($manager, $alias, $name, $params) {
                            return $manager->createThread($alias, $name, $params);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE)
            {
                return $this
                    ->destroyThread($alias, Runtime::DESTROY_FORCE, $params)
                    ->then(
                        function() use($manager, $alias, $name, $params) {
                            return $manager->createThread($alias, $name, $params);
                        }
                    );
            }
            else
            {
                return Promise::doReject(new ResourceOccupiedException('Thread with such alias already exists.'));
            }
        }
        else if ($name === null)
        {
            return Promise::doReject(
                new InvalidArgumentException('Name of new thread cannot be null.')
            );
        }

        global $loader;
        $controller = new ThreadController($loader);
        $wrapper = new ThreadWrapper(
            $controller,
            $this->runtime->getCore()->getDataPath(),
            $this->runtime->getAlias(),
            $alias,
            $name,
            $this->context
        );
        $wrapper->start(PTHREADS_INHERIT_ALL);

        $this->allocateThread($alias, $wrapper);

        $req = $this->createRequest(
            $this->channel, $alias, new RuntimeCommand('cmd:ping', $params)
        );

        return $req->call()
            ->then(
                function() {
                    return 'Thread has been created.';
                },
                function($reason) use($alias) {
                    $this->freeThread($alias);
                    return $reason;
                },
                function($reason) use($alias) {
                    $this->freeThread($alias);
                    return $reason;
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        if (!isset($this->threads[$alias]))
        {
            return Promise::doResolve("Thread [$alias] was not needed to be destroyed, because it had not existed.");
        }

        $thread = $this->threads[$alias];
        $manager = $this;

        if ($flags === Runtime::DESTROY_KEEP)
        {
            return Promise::doReject(
                new ResourceOccupiedException("Thread with alias [$alias] could not be destroyed with force leve=DESTROY_KEEP.")
            );
        }
        else if ($flags === Runtime::DESTROY_FORCE_SOFT)
        {
            $req = $this->createRequest(
                $this->channel,
                $alias,
                new RuntimeCommand('container:destroy', $params)
            );

            return $req->call()
                ->then(
                    function($value) use($manager, $alias) {
                        usleep(1e3);
                        $manager->freeThread($alias);
                        return $value;
                    }
                );
        }
        else if ($flags === Runtime::DESTROY_FORCE)
        {
            $manager = $this;
            return $manager
                ->destroyThread($alias, Runtime::DESTROY_FORCE_SOFT, $params)
                ->then(
                    null,
                    function() use($manager, $alias, $params) {
                        return $manager->destroyThread($alias, Runtime::DESTROY_FORCE_HARD, $params);
                    }
                );
        }

        if (!$thread->kill())
        {
            return Promise::doReject(
                new ResourceOccupiedException("Thread [$alias] could not be killed forcefully.")
            );
        }

        return Promise::doResolve()
            ->then(
                function() use($manager, $alias) {
                    $manager->freeThread($alias);
                    return "Thread has been destroyed!";
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThread($alias, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $alias,
            new RuntimeCommand('container:start', $params)
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThread($alias, $params = [])
    {
        $req = $this->createRequest(
            $this->channel,
            $alias,
            new RuntimeCommand('container:stop', $params)
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT, $params = [])
    {
        $promises = [];

        foreach ($definitions as $alias=>$name)
        {
            $promises[] = $this->createThread($alias, $name, $flags, $params);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    return 'Threads have been created.';
                },
                function() {
                    throw new RejectionException('Some of the threads could not be created.');
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT, $params = [])
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->destroyThread($alias, $flags, $params);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    return 'Threads have been destroyed.';
                },
                function() {
                    throw new RejectionException('Some of the threads could not be destroyed.');
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startThreads($aliases, $params = [])
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->startThread($alias, $params);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    return 'Threads have been started.';
                },
                function() {
                    throw new RejectionException('Some of the threads could not be started.');
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopThreads($aliases, $params = [])
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->stopThread($alias, $params);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    return 'Threads have been stopped.';
                },
                function() {
                    throw new RejectionException('Some of the threads could not be stopped.');
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getThreads()
    {
        $list = [];
        foreach ($this->threads as $alias=>$wrapper)
        {
            $list[] = $alias;
        }

        return Promise::doResolve($list);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP)
    {
        $promises = [];

        if ($flags === Runtime::DESTROY_KEEP)
        {
            return Promise::doReject(
                new RejectionException('Threads storage could not be flushed because of force level set to DESTROY_KEEP.')
            );
        }

        foreach ($this->threads as $alias=>$process)
        {
            $promises[] = $this->destroyThread($alias, $flags);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    $this->threads = [];
                    return 'Threads storage has been flushed.';
                }
            );
    }

    /**
     * Allocate thread data.
     *
     * @internal
     * @param string $alias
     * @param mixed $object
     * @return bool
     */
    public function allocateThread($alias, $object)
    {
        $this->threads[$alias] = $object;
        return true;
    }

    /**
     * Free thread data.
     *
     * @internal
     * @param string $alias
     * @return bool
     */
    public function freeThread($alias)
    {
        unset($this->threads[$alias]);
        return true;
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
