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
     * @return bool
     */
    public function isRunning()
    {
        return $this->isRunning;
    }

    /**
     *
     */
    public function start()
    {
        $this->isRunning = true;
    }

    /**
     *
     */
    public function stop()
    {
        $this->isRunning = false;
    }
}
