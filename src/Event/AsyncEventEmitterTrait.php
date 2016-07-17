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
            $this->getLoop()->afterTick(function() use($listener, $args) {
                call_user_func_array($listener, $args);
            });
        };
    }

    /**
     * @see BaseEventEmitterTrait::attachOnceListener
     */
    protected function attachOnceListener($pointer, $event, callable $listener)
    {
        return function() use($listener, $event, $pointer) {
            unset($this->emitterEventHandlers[$event][$pointer]);

            $args = func_get_args();
            $this->getLoop()->afterTick(function() use($listener, $args) {
                call_user_func_array($listener, $args);
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
                unset($emitter->emitterEventHandlers[$event][$pointer]);
            }

            $args = func_get_args();
            $this->getLoop()->afterTick(function() use($listener, $args) {
                call_user_func_array($listener, $args);
            });
        };
    }
}
