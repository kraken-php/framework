<?php

namespace Kraken\Event;

use Kraken\Loop\LoopInterface;

trait EventEmitterTrait
{
    /**
     * @var EventEmitterInterface
     */
    protected $emitter;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop = null)
    {
        if ($loop !== null)
        {
            $this->emitter = new AsyncEventEmitter($loop);
        }
        else
        {
            $this->emitter = new BaseEventEmitter();
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->emitter);
    }

    /**
     * @param int $emitterBlocked
     */
    public function setBlocking($emitterBlocked)
    {
        $this->emitter->setBlocking($emitterBlocked);
    }

    /**
     * @return EventEmitterInterface
     */
    public function emitter()
    {
        return $this->emitter->emitter();
    }

    /**
     * @param string $event
     * @param callable $listener
     * @return EventHandler
     */
    public function on($event, callable $listener)
    {
        return $this->emitter->on($event, $listener);
    }

    /**
     * @param string $event
     * @param callable $listener
     * @return EventHandler
     */
    public function once($event, callable $listener)
    {
        return $this->emitter->once($event, $listener);
    }

    /**
     * @param string $event
     * @param callable $listener
     */
    public function removeListener($event, callable $listener)
    {
        $this->emitter->removeListener($event, $listener);
    }

    /**
     * @param string|null $event
     */
    public function removeAllListeners($event = null)
    {
        $this->emitter->removeAllListeners($event);
    }

    /**
     * @param string $event
     * @param callable $listener
     * @return int|null
     */
    public function findListener($event, callable $listener)
    {
        return $this->emitter->findListener($event, $listener);
    }

    /**
     * @param string $event
     * @param mixed[] $arguments
     */
    public function emit($event, $arguments = [])
    {
        $this->emitter->emit($event, $arguments);
    }

    /**
     * @param EventEmitterInterface $emitter
     * @param string $event
     * @return EventHandler
     */
    public function copyEvent(EventEmitterInterface $emitter, $event)
    {
        return $this->emitter->copyEvent($emitter, $event);
    }

    /**
     * @param EventEmitterInterface $emitter
     * @param string[] $events
     * @return EventHandler[]
     */
    public function copyEvents(EventEmitterInterface $emitter, $events)
    {
        return $this->emitter->copyEvents($emitter, $events);
    }

    /**
     * @param EventEmitterInterface $emitter
     * @return EventEmitterInterface
     */
    public function forwardEvents(EventEmitterInterface $emitter)
    {
        return $this->emitter->forwardEvents($emitter);
    }

    /**
     * @param EventEmitterInterface $emitter
     * @return EventEmitterInterface
     */
    public function discardEvents(EventEmitterInterface $emitter)
    {
        return $this->emitter->discardEvents($emitter);
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function addEventEmitterForwarder(EventEmitterInterface $emitter)
    {
        $this->emitter->addEventEmitterForwarder($emitter);
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function addEventEmitterListener(EventEmitterInterface $emitter)
    {
        $this->emitter->addEventEmitterListener($emitter);
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function removeEventEmitterForwarder(EventEmitterInterface $emitter)
    {
        $this->emitter->removeEventEmitterForwarder($emitter);
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function removeEventEmitterListener(EventEmitterInterface $emitter)
    {
        $this->emitter->removeEventEmitterListener($emitter);
    }

    /**
     * @param EventEmitterInterface $emitter
     * @return int|null
     */
    public function findEventEmitterForwarder(EventEmitterInterface $emitter)
    {
        return $this->emitter->findEventEmitterForwarder($emitter);
    }

    /**
     * @param EventEmitterInterface $emitter
     * @return int|null
     */
    public function findEventEmitterListener(EventEmitterInterface $emitter)
    {
        return $this->emitter->findEventEmitterListener($emitter);
    }
}