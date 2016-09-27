<?php

namespace Kraken\Event;

interface EventEmitterInterface
{
    /**
     * Set mode for EventEmitter behaviour.
     *
     * Set mode for EventEmitter behaviour. $emitterMode can be one of:
     * EventEmitter::EVENTS_FORWARD Allows all events to be forwarded (Default)
     * EventEmitter::EVENTS_DISCARD Disallows all events from being forwarded
     * EventEmitter::EVENTS_DISCARD_INCOMING Discards only listeners attached to $this emitter
     * EventEmitter::EVENTS_DISCARD_OUTCOMING Discards only further emits on forwarder
     *
     * @param int $emitterMode
     */
    public function setMode($emitterMode);

    /**
     * Returns mode of EventEmitter behaviour.
     *
     * @see setMode
     * @return int
     */
    public function getMode();

    /**
     * Set listener for event. This method returns EventListener.
     *
     * @param string $event
     * @param callable $listener
     * @return EventListener
     */
    public function on($event, callable $listener);

    /**
     * Set listener for event that will fire only once. This method returns EventListener
     *
     * @param string $event
     * @param callable $listener
     * @return EventListener
     */
    public function once($event, callable $listener);

    /**
     * Set listener for event that will fire at most as many times as set limit to. This method returns EventListener
     *
     * @param string $event
     * @param int $limit
     * @param callable $listener
     * @return EventListener
     */
    public function times($event, $limit, callable $listener);

    /**
     * Set listener for event that will start to be invoked after $ticks number of events is emitted. This method
     * returns EventListener.
     *
     * @param string $event
     * @param int $ticks
     * @param callable $listener
     * @return EventListener
     */
    public function delay($event, $ticks, callable $listener);

    /**
     * Set listener for event that will fire only once after $ticks number of events is emitted. This method returns
     * EventListener
     *
     * @param string $event
     * @param int $ticks
     * @param callable $listener
     * @return EventListener
     */
    public function delayOnce($event, $ticks, callable $listener);

    /**
     * Set listener for event that will fire at most as many times as $limit is set to, but only after $ticks number of
     * events is emitted. This method returns EventListener
     *
     * @param string $event
     * @param int $limit
     * @param callable $listener
     * @return EventListener
     */
    public function delayTimes($event, $ticks, $limit, callable $listener);

    /**
     * Remove existing listener for event.
     *
     * @param string $event
     * @param callable $listener
     */
    public function removeListener($event, callable $listener);

    /**
     * Remove all listeners for event.
     *
     * @param string $event
     */
    public function removeListeners($event);

    /**
     * Remove all listeners.
     */
    public function flushListeners();

    /**
     * Find listener for event.
     *
     * Find listener for event. Returns int greater or equal 0 if listener is found or null if not.
     *
     * @param string $event
     * @param callable $listener
     * @return int|null
     */
    public function findListener($event, callable $listener);

    /**
     * Emit event with specified arguments.
     *
     * @param string $event
     * @param mixed[] $arguments
     */
    public function emit($event, $arguments = []);

    /**
     * Forward event to another emitter.
     *
     * @param EventEmitterInterface $emitter
     * @param string $event
     * @return EventListener
     */
    public function copyEvent(EventEmitterInterface $emitter, $event);

    /**
     * Forward set of events to another emitter.
     *
     * @param EventEmitterInterface $emitter
     * @param string[] $events
     * @return EventListener[]
     */
    public function copyEvents(EventEmitterInterface $emitter, $events);

    /**
     * Forward all events to another emitter.
     *
     * @param EventEmitterInterface $emitter
     * @return EventEmitterInterface
     */
    public function forwardEvents(EventEmitterInterface $emitter);

    /**
     * Discard events previously forwarded to another emitter.
     *
     * @param EventEmitterInterface $emitter
     * @return EventEmitterInterface
     */
    public function discardEvents(EventEmitterInterface $emitter);
}
