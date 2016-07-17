<?php

namespace Kraken\Event;

interface EventEmitterAwareInterface
{
    /**
     * Return EventEmitter of which component is aware of.
     *
     * @return EventEmitterInterface
     */
    public function getEventEmitter();

    /**
     * Set EventEmitter of which component is aware of.
     *
     * @param EventEmitterInterface $emitter
     */
    public function setEventEmitter(EventEmitterInterface $emitter);

    /**
     * @see getEventEmitter
     */
    public function eventEmitter();
}
