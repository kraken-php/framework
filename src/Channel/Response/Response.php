<?php

namespace Kraken\Channel\Response;

class Response
{
    /**
     * @var string
     */
    public $pid;

    /**
     * @var string
     */
    public $alias;

    /**
     * @var float
     */
    public $timeout;

    /**
     * @var float
     */
    public $timeoutIncrease;

    /**
     * @param string $pid
     * @param string $alias
     * @param float $timeout
     * @param float $timeoutIncrease
     */
    public function __construct($pid, $alias, $timeout = 0.0, $timeoutIncrease = 1.0)
    {
        $this->pid             = $pid;
        $this->alias           = $alias;
        $this->timeout         = $timeout;
        $this->timeoutIncrease = $timeoutIncrease;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->pid);
        unset($this->alias);
        unset($this->timeout);
        unset($this->timeoutIncrease);
    }

    /**
     * @return string
     */
    public function pid()
    {
        return $this->pid;
    }

    /**
     * @return string
     */
    public function alias()
    {
        return $this->alias;
    }

    /**
     * @return float
     */
    public function timeout()
    {
        return $this->timeout;
    }

    /**
     * @return float
     */
    public function timeoutIncrease()
    {
        return $this->timeoutIncrease;
    }
}
