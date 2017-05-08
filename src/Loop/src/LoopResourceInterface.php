<?php

namespace Kraken\Loop;

interface LoopResourceInterface extends LoopAwareInterface
{
    /**
     * Check if resource is paused or not.
     *
     * This method allow to check whether the object is in paused state, meaning all of streams it contains are detached
     * from loop or are they still active.
     *
     * @return bool
     */
    public function isPaused();

    /**
     * Temporarily pause the resource.
     *
     * This method allows to detach all resources contained within the object from writable and readable streams of
     * active loop, preventing from writing or reading any data done by it.
     */
    public function pause();

    /**
     * Resume the resource.
     *
     * This method allows to reattach all resources of given object back to its original writable and readable
     * streams of active loop.
     */
    public function resume();
}
