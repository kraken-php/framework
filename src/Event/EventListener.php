<?php

namespace Kraken\Event;

class EventListener
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
     * @return callable|null
     */
    public function getHandler()
    {
        return isset($this->handler) ? $this->handler : null;
    }

    /**
     * @return callable
     */
    public function getListener()
    {
        return isset($this->listener) ? $this->listener : function() {};
    }

    /**
     *
     */
    public function cancel()
    {
        if (isset($this->emitter))
        {
            $this->emitter->removeListener($this->getEvent(), $this->getHandler());
        }
    }
}
