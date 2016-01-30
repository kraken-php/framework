<?php

namespace Kraken\Channel\Router;

use Kraken\Channel\ChannelProtocolInterface;

class RuleMatchPid
{
    /**
     * @var string
     */
    protected $pid;

    /**
     * @param string $pid
     */
    public function __construct($pid)
    {
        $this->pid = $pid;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->pid);
    }

    /**
     * @param string $name
     * @param ChannelProtocolInterface $protocol
     * @return bool
     */
    public function __invoke($name, ChannelProtocolInterface $protocol)
    {
        return $protocol->getPid() === $this->pid;
    }
}
