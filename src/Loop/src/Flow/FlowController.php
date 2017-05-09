<?php

namespace Kraken\Loop\Flow;

class FlowController
{
    /**
     * @var bool
     */
    public $isRunning;

    /**
     *
     */
    public function __construct()
    {
        $this->isRunning = false;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->isRunning);
    }

    /**
     * Check if FlowController allows loop to run.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->isRunning;
    }

    /**
     * Set FlowController to allow loop to run.
     */
    public function start()
    {
        $this->isRunning = true;
    }

    /**
     * Set FlowController to not allow loop to run.
     */
    public function stop()
    {
        $this->isRunning = false;
    }
}
