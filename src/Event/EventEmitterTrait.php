<?php

namespace Kraken\Event;

use Kraken\Loop\LoopInterface;

trait EventEmitterTrait
{
    /**
     * @var EventEmitterInterface
     */
    protected $emitter;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop = null)
    {
        if ($loop !== null)
        {
            $this->emitter = new AsyncEventEmitter($loop);
        }
        else
        {
            $this->emitter = new BaseEventEmitter();
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->emitter);
    }

    /**
     * @see EventEmitterInterface::setMode
     */
    public function setMode($emitterMode)
    {
        $this->emitter->setMode($emitterMode);
    }

    /**
     * @see EventEmitterInterface::getMode
     */
    public function getMode()
    {
        return $this->emitter->getMode();
    }

    /**
     * @see EventEmitterInterface::on
     */
    public function on($event, callable $listener)
    {
        return $this->emitter->on($event, $listener);
    }

    /**
     * @see EventEmitterInterface::once
     */
    public function once($event, callable $listener)
    {
        return $this->emitter->once($event, $listener);
    }

    /**
     * @see EventEmitterInterface::removeListener
     */
    public function removeListener($event, callable $listener)
    {
        $this->emitter->removeListener($event, $listener);
    }

    /**
     * @see EventEmitterInterface::removeListeners
     */
    public function removeListeners($event)
    {
        $this->emitter->removeListeners($event);
    }

    /**
     * @see EventEmitterInterface::removeAllListeners
     */
    public function removeAllListeners()
    {
        $this->emitter->removeAllListeners();
    }

    /**
     * @see EventEmitterInterface::findListener
     */
    public function findListener($event, callable $listener)
    {
        return $this->emitter->findListener($event, $listener);
    }

    /**
     * @see EventEmitterInterface::emit
     */
    public function emit($event, $arguments = [])
    {
        $this->emitter->emit($event, $arguments);
    }

    /**
     * @see EventEmitterInterface::copyEvent
     */
    public function copyEvent(EventEmitterInterface $emitter, $event)
    {
        return $this->emitter->copyEvent($emitter, $event);
    }

    /**
     * @see EventEmitterInterface::copyEvents
     */
    public function copyEvents(EventEmitterInterface $emitter, $events)
    {
        return $this->emitter->copyEvents($emitter, $events);
    }

    /**
     * @see EventEmitterInterface::forwardEvents
     */
    public function forwardEvents(EventEmitterInterface $emitter)
    {
        return $this->emitter->forwardEvents($emitter);
    }

    /**
     * @see EventEmitterInterface::discardEvents
     */
    public function discardEvents(EventEmitterInterface $emitter)
    {
        return $this->emitter->discardEvents($emitter);
    }
}