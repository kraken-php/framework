<?php

namespace Kraken\Runtime\Container\Thread;

use Threaded;

class ThreadController extends Threaded
{
    /**
     * @var bool
     */
    public $killed;

    /**
     * @var bool
     */
    public $isRunning;

    /**
     *
     */
    public function __construct()
    {
        $this->killed = false;
        $this->isRunning = true;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->killed);
        unset($this->isRunning);
    }

    /**
     * @return bool
     */
    public function kill()
    {
        if ($this->killed)
        {
            return false;
        }

        $this->killed = true;
        $this->isRunning = false;

        return false;
    }
}
