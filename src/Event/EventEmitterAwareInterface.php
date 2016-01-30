<?php

namespace Kraken\Event;

interface EventEmitterAwareInterface
{
    /**
     * @return EventEmitterInterface
     */
    public function getEventEmitter();

    /**
     * @param EventEmitterInterface $emitter
     */
    public function setEventEmitter(EventEmitterInterface $emitter);

    /**
     * @return EventEmitterInterface
     */
    public function eventEmitter();
}
