<?php

namespace Kraken\Event;

interface EventEmitterAwareInterface
{
    /**
     * Set EventEmitter of which component is aware of.
     *
     * @param EventEmitterInterface|null $emitter
     */
    public function setEventEmitter(EventEmitterInterface $emitter = null);

    /**
     * Return EventEmitter of which component is aware of.
     *
     * @return EventEmitterInterface|null
     */
    public function getEventEmitter();
}
