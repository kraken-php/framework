<?php

namespace Kraken\Event;

use Kraken\Loop\LoopAwareTrait;

trait AsyncEventEmitterTrait
{
    use LoopAwareTrait;
    use BaseEventEmitterTrait;

    /**
     * @see BaseEventEmitterTrait::attachOnListener
     */
    protected function attachOnListener($pointer, $event, callable $listener)
    {
        return function() use($listener) {
            $args = func_get_args();
            $this->getLoop()->onTick(function() use($listener, $args) {
                $listener(...$args);
            });
        };
    }

    /**
     * @see BaseEventEmitterTrait::attachOnceListener
     */
    protected function attachOnceListener($pointer, $event, callable $listener)
    {
        return function() use($listener, $event, $pointer) {
            unset($this->eventListeners[$event][$pointer]);

            $args = func_get_args();
            $this->getLoop()->onTick(function() use($listener, $args) {
                $listener(...$args);
            });
        };
    }

    /**
     * @see BaseEventEmitterTrait::attachTimesListener
     */
    protected function attachTimesListener($pointer, $event, $limit, callable $listener)
    {
        $emitter = $this;
        return function() use($emitter, $listener, $event, $pointer, &$limit) {
            if (--$limit === 0)
            {
                unset($limit);
                unset($emitter->eventListeners[$event][$pointer]);
            }

            $args = func_get_args();
            $this->getLoop()->onTick(function() use($listener, $args) {
                $listener(...$args);
            });
        };
    }
}
