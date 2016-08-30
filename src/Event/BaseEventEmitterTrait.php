<?php

namespace Kraken\Event;

trait BaseEventEmitterTrait
{
    /**
     * @var int
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
        unset($this->emitterListeners);
    }

    /**
     * @see EventEmitterInterface::setMode
     */
    public function setMode($emitterMode)
    {
        $this->emitterBlocked = $emitterMode;
    }

    /**
     * @see EventEmitterInterface::getMode
     */
    public function getMode()
    {
        return $this->emitterBlocked;
    }

    /**
     * @see EventEmitterInterface::on
     */
    public function on($event, callable $listener)
    {
        if (!isset($this->emitterEventHandlers[$event]))
        {
            $this->emitterPointer[$event] = 0;
            $this->emitterEventHandlers[$event] = [];
        }

        $pointer = &$this->emitterPointer[$event];
        $eventListener = new EventHandler($this, $event, $listener, $this->attachOnListener($pointer, $event, $listener));

        $this->emitterEventHandlers[$event][$pointer++] = $eventListener;

        return $eventListener;
    }

    /**
     * @see EventEmitterInterface::once
     */
    public function once($event, callable $listener)
    {
        if (!isset($this->emitterEventHandlers[$event]))
        {
            $this->emitterPointer[$event] = 0;
            $this->emitterEventHandlers[$event] = [];
        }

        $pointer = &$this->emitterPointer[$event];
        $eventListener = new EventHandler($this, $event, $listener, $this->attachOnceListener($pointer, $event, $listener));

        $this->emitterEventHandlers[$event][$pointer++] = $eventListener;

        return $eventListener;
    }

    /**
     * @see EventEmitterInterface::times
     */
    public function times($event, $limit, callable $listener)
    {
        if ($limit === 0)
        {
            return $this->on($event, $listener);
        }

        if (!isset($this->emitterEventHandlers[$event]))
        {
            $this->emitterPointer[$event] = 0;
            $this->emitterEventHandlers[$event] = [];
        }

        $pointer = &$this->emitterPointer[$event];
        $limit = $limit > 0 ? $limit : 1;
        $eventListener = new EventHandler(
            $this,
            $event,
            $listener,
            $this->attachTimesListener($pointer, $event, $limit, $listener)
        );

        $this->emitterEventHandlers[$event][$pointer++] = $eventListener;

        return $eventListener;
    }

    /**
     * @see EventEmitterInterface::delay
     */
    public function delay($event, $ticks, callable $listener)
    {
        $counter = 0;
        return $this->on($event, function() use(&$counter, $event, $ticks, $listener) {
            if (++$counter >= $ticks)
            {
                call_user_func_array($listener, func_get_args());
            }
        });
    }

    /**
     * @see EventEmitterInterface::delayOnce
     */
    public function delayOnce($event, $ticks, callable $listener)
    {
        $counter = 0;
        return $this->times($event, $ticks, function() use(&$counter, $event, $ticks, $listener) {
            if (++$counter >= $ticks)
            {
                call_user_func_array($listener, func_get_args());
            }
        });
    }

    /**
     * @see EventEmitterInterface::delayTimes
     */
    public function delayTimes($event, $ticks, $limit, callable $listener)
    {
        $counter = 0;
        return $this->times($event, $ticks+$limit-1, function() use(&$counter, $event, $ticks, $listener) {
            if (++$counter >= $ticks)
            {
                call_user_func_array($listener, func_get_args());
            }
        });
    }

    /**
     * @see EventEmitterInterface::removeListener
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
     * @see EventEmitterInterface::removeListeners
     */
    public function removeListeners($event)
    {
        unset($this->emitterPointer[$event]);
        unset($this->emitterEventHandlers[$event]);
    }

    /**
     * @see EventEmitterInterface::flushListeners
     */
    public function flushListeners()
    {
        unset($this->emitterPointer);
        unset($this->emitterEventHandlers);

        $this->emitterPointer = [];
        $this->emitterEventHandlers = [];
    }

    /**
     * @see EventEmitterInterface::findListener
     */
    public function findListener($event, callable $listener)
    {
        $listeners = isset($this->emitterEventHandlers[$event]) ? $this->emitterEventHandlers[$event] : [];

        foreach ($listeners as $index=>$eventListener)
        {
            if ($listener === $eventListener->getHandler())
            {
                return $index;
            }
        }

        return null;
    }

    /**
     * @see EventEmitterInterface::emit
     */
    public function emit($event, $arguments = [])
    {
        $listeners = isset($this->emitterEventHandlers[$event]) ? $this->emitterEventHandlers[$event] : [];

        if (($this->emitterBlocked & EventEmitter::EVENTS_DISCARD_INCOMING) !== EventEmitter::EVENTS_DISCARD_INCOMING)
        {
            foreach ($listeners as $eventListener)
            {
                call_user_func_array($eventListener->getListener(), $arguments);
            }
        }

        if (($this->emitterBlocked & EventEmitter::EVENTS_DISCARD_OUTCOMING) !== EventEmitter::EVENTS_DISCARD_OUTCOMING)
        {
            foreach ($this->emitterListeners as $listener)
            {
                $listener->emit($event, $arguments);
            }
        }
    }

    /**
     * @see EventEmitterInterface::copyEvent
     */
    public function copyEvent(EventEmitterInterface $emitter, $event)
    {
        return $this->on($event, function() use($emitter, $event) {
            $emitter->emit($event, func_get_args());
        });
    }

    /**
     * @see EventEmitterInterface::copyEvents
     */
    public function copyEvents(EventEmitterInterface $emitter, $events)
    {
        $handlers = [];
        $events = (array) $events;

        foreach ($events as $event)
        {
            $handlers[] = $this->copyEvent($emitter, $event);
        }

        return $handlers;
    }

    /**
     * @see EventEmitterInterface::forwardEvents
     */
    public function forwardEvents(EventEmitterInterface $emitter)
    {
        $this->emitterListeners[] = $emitter;

        return $emitter;
    }

    /**
     * @see EventEmitterInterface::discardEvents
     */
    public function discardEvents(EventEmitterInterface $emitter)
    {
        foreach ($this->emitterListeners as $index=>$listener)
        {
            if ($listener === $emitter)
            {
                unset($this->emitterListeners[$index]);
            }
        }

        return $emitter;
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

    /**
     * @param int $pointer
     * @param string $event
     * @param int $limit
     * @param callable $listener
     * @return callable
     */
    protected function attachTimesListener($pointer, $event, $limit, callable $listener)
    {
        $emitter = $this;
        return function() use($emitter, $listener, $event, $pointer, &$limit) {
            if (--$limit === 0)
            {
                unset($limit);
                unset($emitter->emitterEventHandlers[$event][$pointer]);
            }

            return call_user_func_array($listener, func_get_args());
        };
    }
}
