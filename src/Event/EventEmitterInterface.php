<?php

namespace Kraken\Event;

interface EventEmitterInterface
{
    /**
     * @param int $emitterBlocked
     */
    public function setBlocking($emitterBlocked);

    /**
     * @return EventEmitterInterface
     */
    public function emitter();

    /**
     * @param string $event
     * @param callable $listener
     * @return EventHandler
     */
    public function on($event, callable $listener);

    /**
     * @param string $event
     * @param callable $listener
     * @return EventHandler
     */
    public function once($event, callable $listener);

    /**
     * @param string $event
     * @param callable $listener
     */
    public function removeListener($event, callable $listener);

    /**
     * @param string|null $event
     */
    public function removeAllListeners($event = null);

    /**
     * @param string $event
     * @param callable $listener
     * @return int|null
     */
    public function findListener($event, callable $listener);

    /**
     * @param string $event
     * @param mixed[] $arguments
     */
    public function emit($event, $arguments = []);

    /**
     * @param EventEmitterInterface $emitter
     * @param string $event
     * @return EventHandler
     */
    public function copyEvent(EventEmitterInterface $emitter, $event);

    /**
     * @param EventEmitterInterface $emitter
     * @param string[] $events
     * @return EventHandler[]
     */
    public function copyEvents(EventEmitterInterface $emitter, $events);

    /**
     * @param EventEmitterInterface $emitter
     * @return EventEmitterInterface
     */
    public function forwardEvents(EventEmitterInterface $emitter);

    /**
     * @param EventEmitterInterface $emitter
     * @return EventEmitterInterface
     */
    public function discardEvents(EventEmitterInterface $emitter);

    /**
     * @param EventEmitterInterface $emitter
     */
    public function addEventEmitterForwarder(EventEmitterInterface $emitter);

    /**
     * @param EventEmitterInterface $emitter
     */
    public function addEventEmitterListener(EventEmitterInterface $emitter);

    /**
     * @param EventEmitterInterface $emitter
     */
    public function removeEventEmitterForwarder(EventEmitterInterface $emitter);

    /**
     * @param EventEmitterInterface $emitter
     */
    public function removeEventEmitterListener(EventEmitterInterface $emitter);

    /**
     * @param EventEmitterInterface $emitter
     * @return int|null
     */
    public function findEventEmitterForwarder(EventEmitterInterface $emitter);

    /**
     * @param EventEmitterInterface $emitter
     * @return int|null
     */
    public function findEventEmitterListener(EventEmitterInterface $emitter);
}
