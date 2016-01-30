<?php

namespace Kraken\Runtime\Thread\Manager;

use Kraken\Exception\Runtime\InvalidArgumentException;
use Kraken\Exception\Runtime\RejectionException;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Exception\Resource\ResourceDefinedException;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\RuntimeInterface;
use Kraken\Runtime\Thread\ThreadManagerInterface;
use Kraken\Runtime\Thread\ThreadWrapper;

class ThreadManagerBase implements ThreadManagerInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var ThreadWrapper[]
     */
    protected $threads;

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelBaseInterface $channel
     */
    public function __construct(RuntimeInterface $runtime, ChannelBaseInterface $channel)
    {
        $this->runtime = $runtime;
        $this->channel = $channel;
        $this->threads = [];
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
        unset($this->channel);
        unset($this->threads);
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function existsThread($alias)
    {
        return isset($this->threads[$alias]);
    }

    /**
     * @param string $alias
     * @param string|null $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThread($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        if (isset($this->threads[$alias]))
        {
            if ($name === null)
            {
                $name = $this->threads[$alias]->name;
            }

            if ($flags === Runtime::CREATE_FORCE_SOFT)
            {
                $manager = $this;
                return $this->destroyThread($alias, Runtime::DESTROY_FORCE_SOFT)
                    ->then(
                        function() use($manager, $alias, $name) {
                            return $manager->createThread($alias, $name);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE_HARD)
            {
                $manager = $this;
                return $this->destroyThread($alias, Runtime::DESTROY_FORCE_HARD)
                    ->then(
                        function() use($manager, $alias, $name) {
                            return $manager->createThread($alias, $name);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE)
            {
                $manager = $this;
                return $this->destroyThread($alias, Runtime::DESTROY_FORCE)
                    ->then(
                        function() use($manager, $alias, $name) {
                            return $manager->createThread($alias, $name);
                        }
                    );
            }
            else
            {
                return Promise::doReject(new ResourceDefinedException('Thread with such alias already exists.'));
            }
        }
        else if ($name === null)
        {
            return Promise::doReject(
                new InvalidArgumentException('Name of new thread cannot be null.')
            );
        }

        $wrapper = new ThreadWrapper(
            $this->runtime->core()->dataPath(),
            $this->runtime->alias(),
            $alias,
            $name
        );
        $wrapper->start(PTHREADS_INHERIT_ALL);

        $this->allocateThread($alias, $wrapper);

        $req = new Request(
            $this->channel, $alias, new RuntimeCommand('cmd:ping')
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
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThread($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
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
                new ResourceDefinedException("Thread with alias [$alias] could not be destroyed with force leve=DESTROY_KEEP.")
            );
        }
        else if ($flags === Runtime::DESTROY_FORCE_SOFT)
        {
            $req = new Request(
                $this->channel,
                $alias,
                new RuntimeCommand('container:destroy')
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
            return $manager->destroyThread($alias, Runtime::DESTROY_FORCE_SOFT)
                ->then(
                    null,
                    function() use($manager, $alias) {
                        return $manager->destroyThread($alias, Runtime::DESTROY_FORCE_HARD);
                    }
                );
        }

        if (!$thread->kill())
        {
            return Promise::doReject(
                new ResourceDefinedException("Thread [$alias] could not be killed forcefully.")
            );
        }

        return Promise::doResolve()
            ->then(
                function() use($manager, $alias) {
                    $manager->freeThread($alias);
                }
            )
            ->then(
                function() {
                    return "Thread has been destroyed!";
                }
            );
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startThread($alias)
    {
        $req = new Request(
            $this->channel,
            $alias,
            new RuntimeCommand('container:start')
        );

        return $req->call();
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopThread($alias)
    {
        $req = new Request(
            $this->channel,
            $alias,
            new RuntimeCommand('container:stop')
        );

        return $req->call();
    }

    /**
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createThreads($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        $promises = [];

        foreach ($definitions as $alias=>$name)
        {
            $promises[] = $this->createThread($alias, $name, $flags);
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
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyThreads($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->destroyThread($alias, $flags);
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
     * @param $aliases
     * @return PromiseInterface
     */
    public function startThreads($aliases)
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->startThreads($alias);
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
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopThreads($aliases)
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->stopThread($alias);
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
     * @return PromiseInterface
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
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushThreads($flags = Runtime::DESTROY_KEEP)
    {
        $promises = [];

        if ($flags !== Runtime::DESTROY_KEEP)
        {
            foreach ($this->threads as $alias=>$process)
            {
                $promises[] = $this->destroyThread($alias, $flags);
            }
        }

        return Promise::all($promises)
            ->always(function() {
                $this->threads = [];
            })
            ->then(function() {
                return 'Threads storage has been flushed.';
            })
        ;
    }

    /**
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
     * @internal
     * @param string $alias
     * @return bool
     */
    public function freeThread($alias)
    {
        unset($this->threads[$alias]);
        return true;
    }
}
