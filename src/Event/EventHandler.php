<?php

namespace Kraken\Event;

class EventHandler
{
    /**
     * @var EventEmitterInterface
     */
    private $emitter;

    /**
     * @var string
     */
    private $event;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var callable
     */
    private $listener;

    /**
     * @param EventEmitterInterface $emitter
     * @param string $event
     * @param callable $handler
     * @param callable|null $listener
     */
    public function __construct(EventEmitterInterface $emitter, $event, callable $handler, callable $listener = null)
    {
        $this->emitter = $emitter;
        $this->event = $event;
        $this->handler = $handler;
        $this->listener = $listener !== null ? $listener : $handler;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->emitter);
        unset($this->event);
        unset($this->handler);
        unset($this->listener);
    }

    /**
     * @return EventEmitterInterface
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return callable
     */
    public function getListener()
    {
        return $this->listener;
    }

    /**
     *
     */
    public function cancel()
    {
        $this->emitter->removeListener($this->getEvent(), $this->getHandler());
    }
}
