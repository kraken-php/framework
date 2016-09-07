<?php

namespace Kraken\Channel\Router\RuleMatch;

use Kraken\Channel\Protocol\ProtocolInterface;

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
     * @param ProtocolInterface $protocol
     * @return bool
     */
    public function __invoke($name, ProtocolInterface $protocol)
    {
        return $protocol->getPid() === $this->pid;
    }
}
