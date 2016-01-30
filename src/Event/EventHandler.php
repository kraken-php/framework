<?php

namespace Kraken\Event;

class EventHandler
{
    /**
     * @var EventEmitterInterface
     */
    protected $emitter;

    /**
     * @var string
     */
    protected $event;

    /**
     * @var callable
     */
    protected $listener;

    /**
     * @param EventEmitterInterface $emitter
     * @param string $event
     * @param callable $listener
     */
    public function __construct(EventEmitterInterface $emitter, $event, callable $listener)
    {
        $this->emitter = $emitter;
        $this->event = $event;
        $this->listener = $listener;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->emitter);
        unset($this->event);
        unset($this->listener);
    }

    /**
     * @return EventEmitterInterface
     */
    public function emitter()
    {
        return $this->emitter;
    }

    /**
     * @return string
     */
    public function event()
    {
        return $this->event;
    }

    /**
     * @return callable
     */
    public function listener()
    {
        return $this->listener;
    }

    /**
     *
     */
    public function cancel()
    {
        $this->emitter->removeListener($this->event(), $this->listener());
    }
}
