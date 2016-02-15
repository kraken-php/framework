<?php

namespace Kraken\Loop;

interface LoopResourceInterface extends LoopAwareInterface
{
    /**
     * This method temporarily pauses the resource.
     *
     * This method allows to detach all resources contained within the object from writable and readable streams of
     * active loop, preventing from writing or reading any data done by it.
     */
    public function pause();

    /**
     * This method resumes the resource.
     *
     * This method allows to reattach all resources of given object back to its original writable and readable
     * streams of active loop.
     */
    public function resume();
}
