<?php

namespace Kraken\Event;

trait BaseEventEmitterTrait
{
    /**
     * @var bool
     */
    protected $emitterBlocked = EventEmitter::EVENTS_FORWARD;

    /**
     * @var int[]
     */
    protected $emitterPointer = [];

    /**
     * @var EventHandler[][]
     */
    protected $emitterEventHandlers = [];

    /**
     * @var EventEmitterInterface[]
     */
    protected $emitterForwarders = [];

    /**
     * @var EventEmitterInterface[]
     */
    protected $emitterListeners = [];

    /**
     *
     */
    public function __construct()
    {}

    /**
     *
     */
    public function __destruct()
    {
        unset($this->emitterBlocked);
        unset($this->emitterPointer);
        unset($this->emitterEventHandlers);

        foreach ($this->emitterForwarders as $forwarder)
        {
            $forwarder->removeEventEmitterForwarder($this);
            $this->removeEventEmitterListener($forwarder);
        }

        unset($this->emitterForwarders);
        unset($this->emitterListeners);
    }

    /**
     * @param int $emitterBlocked
     */
    public function setBlocking($emitterBlocked)
    {
        $this->emitterBlocked = $emitterBlocked;
    }

    /**
     * @return EventEmitterInterface
     */
    public function emitter()
    {
        return $this;
    }

    /**
     * @param string $event
     * @param callable $listener
     * @return EventHandler
     */
    public function on($event, callable $listener)
    {
        if (!isset($this->emitterEventHandlers[$event]))
        {
            $this->emitterPointer[$event] = 0;
            $this->emitterEventHandlers[$event] = [];
        }

        $pointer = &$this->emitterPointer[$event];
        $eventListener = new EventHandler($this, $event, $this->attachOnListener($pointer, $event, $listener));

        $this->emitterEventHandlers[$event][$pointer++] = $eventListener;

        return $eventListener;
    }

    /**
     * @param string $event
     * @param callable $listener
     * @return EventHandler
     */
    public function once($event, callable $listener)
    {
        if (!isset($this->emitterEventHandlers[$event]))
        {
            $this->emitterPointer[$event] = 0;
            $this->emitterEventHandlers[$event] = [];
        }

        $pointer = &$this->emitterPointer[$event];
        $eventListener = new EventHandler($this, $event, $this->attachOnceListener($pointer, $event, $listener));

        $this->emitterEventHandlers[$event][$pointer++] = $eventListener;

        return $eventListener;
    }

    /**
     * @param string $event
     * @param callable $listener
     */
    public function removeListener($event, callable $listener)
    {
        if (isset($this->emitterEventHandlers[$event]))
        {
            if (null !== $index = $this->findListener($event, $listener));
            {
                unset($this->emitterEventHandlers[$event][$index]);
            }
        }
    }

    /**
     * @param string|null $event
     */
    public function removeAllListeners($event = null)
    {
        if ($event !== null)
        {
            unset($this->emitterPointer[$event]);
            unset($this->emitterEventHandlers[$event]);
        }
        else
        {
            unset($this->emitterPointer);
            unset($this->emitterEventHandlers);

            $this->emitterPointer = [];
            $this->emitterEventHandlers = [];
        }
    }

    /**
     * @param string $event
     * @param callable $listener
     * @return int|null
     */
    public function findListener($event, callable $listener)
    {
        $listeners = isset($this->emitterEventHandlers[$event]) ? $this->emitterEventHandlers[$event] : [];

        foreach ($listeners as $index=>$eventListener)
        {
            if ($listener === $eventListener->listener())
            {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param string $event
     * @param mixed[] $arguments
     */
    public function emit($event, $arguments = [])
    {
        $listeners = isset($this->emitterEventHandlers[$event]) ? $this->emitterEventHandlers[$event] : [];

        if (($this->emitterBlocked & EventEmitter::EVENTS_DISCARD_INCOMING) !== EventEmitter::EVENTS_DISCARD_INCOMING)
        {
            foreach ($listeners as $eventListener)
            {
                call_user_func_array($eventListener->listener(), $arguments);
            }
        }

        if (($this->emitterBlocked & EventEmitter::EVENTS_DISCARD_OUTCOMING) !== EventEmitter::EVENTS_DISCARD_OUTCOMING)
        {
            foreach ($this->emitterListeners as $forwarder)
            {
                $forwarder->emit($event, $arguments);
            }
        }
    }

    /**
     * @param EventEmitterInterface $emitter
     * @param string $event
     * @return EventHandler
     */
    public function copyEvent(EventEmitterInterface $emitter, $event)
    {
        return $this->on($event, function() use($emitter, $event) {
            $emitter->emit($event, func_get_args());
        });
    }

    /**
     * @param EventEmitterInterface $emitter
     * @param string[] $events
     * @return EventHandler[]
     */
    public function copyEvents(EventEmitterInterface $emitter, $events)
    {
        $handlers = [];

        foreach ($events as $event)
        {
            $handlers[] = $this->copyEvent($emitter, $event);
        }

        return $handlers;
    }

    /**
     * @param EventEmitterInterface $emitter
     * @return EventEmitterInterface
     */
    public function forwardEvents(EventEmitterInterface $emitter)
    {
        $this->addEventEmitterListener($emitter);
        $emitter->addEventEmitterForwarder($this);

        return $emitter;
    }

    /**
     * @param EventEmitterInterface $emitter
     * @return EventEmitterInterface
     */
    public function discardEvents(EventEmitterInterface $emitter)
    {
        $this->removeEventEmitterListener($emitter);
        $emitter->removeEventEmitterForwarder($this);

        return $emitter;
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function addEventEmitterForwarder(EventEmitterInterface $emitter)
    {
        $this->emitterForwarders[] = $emitter;
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function addEventEmitterListener(EventEmitterInterface $emitter)
    {
        $this->emitterListeners[] = $emitter;
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function removeEventEmitterForwarder(EventEmitterInterface $emitter)
    {
        if (null !== $index = $this->findEventEmitterForwarder($emitter));
        {
            unset($this->emitterForwarders[$index]);
        }
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function removeEventEmitterListener(EventEmitterInterface $emitter)
    {
        if (null !== $index = $this->findEventEmitterListener($emitter));
        {
            unset($this->emitterListeners[$index]);
        }
    }

    /**
     * @param EventEmitterInterface $emitter
     * @return int|null
     */
    public function findEventEmitterForwarder(EventEmitterInterface $emitter)
    {
       foreach ($this->emitterForwarders as $index=>$forwarder)
        {
            if ($forwarder === $emitter)
            {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param EventEmitterInterface $emitter
     * @return int|null
     */
    public function findEventEmitterListener(EventEmitterInterface $emitter)
    {
        foreach ($this->emitterListeners as $index=>$forwarder)
        {
            if ($forwarder === $emitter)
            {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param int $pointer
     * @param string $event
     * @param callable $listener
     * @return callable
     */
    protected function attachOnListener($pointer, $event, callable $listener)
    {
        return $listener;
    }

    /**
     * @param int $pointer
     * @param string $event
     * @param callable $listener
     * @return callable
     */
    protected function attachOnceListener($pointer, $event, callable $listener)
    {
        $emitter = $this;
        return function() use($emitter, $listener, $event, $pointer) {
            unset($emitter->emitterEventHandlers[$event][$pointer]);

            return call_user_func_array($listener, func_get_args());
        };
    }
}
