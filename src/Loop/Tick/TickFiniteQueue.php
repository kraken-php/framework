<?php

namespace Kraken\Loop\Tick;

use Kraken\Loop\LoopModelInterface;
use SplQueue;

class TickFiniteQueue
{
    /**
     * @var LoopModelInterface
     */
    protected $loop;

    /**
     * @var SplQueue
     */
    protected $queue;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @param LoopModelInterface $loop
     */
    public function __construct(LoopModelInterface $loop)
    {
        $this->loop = $loop;
        $this->queue = new SplQueue();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->loop);
        unset($this->queue);
    }

    /**
     * Add a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued, before any timer or stream events.
     *
     * @param callable $listener
     */
    public function add(callable $listener)
    {
        $this->queue->enqueue($listener);
    }

    /**
     * Flush the callback queue.
     *
     * Invokes as many callbacks as were on the queue when tick() was called.
     */
    public function tick()
    {
        $count = $this->queue->count();

        while ($count-- && $this->loop->isRunning())
        {
            $this->callback = $this->queue->dequeue();
            $callback = $this->callback; // without this proxy PHPStorm marks line as fatal error.
            $callback($this->loop);
        }
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
