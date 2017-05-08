<?php

namespace Kraken\Channel\Record;

class ResponseRecord
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
     * Return protocol ID.
     *
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Return alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return timeout.
     *
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Increase timeout.
     *
     * @return float
     */
    public function getTimeoutIncrease()
    {
        return $this->timeoutIncrease;
    }
}
