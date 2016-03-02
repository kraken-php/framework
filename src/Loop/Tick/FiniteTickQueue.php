<?php

namespace Kraken\Loop\Tick;

use SplQueue;
use Kraken\Loop\LoopModelInterface;

class FiniteTickQueue
{
    private $eventLoop;
    private $queue;
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param LoopModelInterface $eventLoop The event loop passed as the first parameter to callbacks.
     */
    public function __construct(LoopModelInterface $eventLoop)
    {
        $this->eventLoop = $eventLoop;
        $this->queue = new SplQueue();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->eventLoop);
        unset($this->queue);
    }

    /**
     * Add a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued.
     *
     * @param callable $listener The callback to invoke.
     */
    public function add(callable $listener)
    {
        $this->queue->enqueue($listener);
    }

    /**
     * Flush the callback queue.
     */
    public function tick()
    {
        // Only invoke as many callbacks as were on the queue when tick() was called.
        $count = $this->queue->count();

//        var_dump('tick=' . $count);

        while ($count-- && $this->eventLoop->isRunning())
        {
            $this->callback = $this->queue->dequeue();
            $callback = $this->callback;
            $callback($this->eventLoop);
        }

//        var_dump('tick=' . $count);
    }

    /**
     * Check if the next tick queue is empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->queue->isEmpty();
    }
}
