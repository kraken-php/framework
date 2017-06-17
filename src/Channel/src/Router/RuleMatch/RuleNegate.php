<?php

namespace Kraken\Channel\Router\RuleMatch;

use Kraken\Channel\Protocol\ProtocolInterface;

class RuleNegate
{
    /**
     * @var callable
     */
    protected $rule;

    /**
     * @param callable $rule
     */
    public function __construct(callable $rule)
    {
        $this->rule = $rule;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->rule);
    }

    /**
     * @param string $name
     * @param ProtocolInterface $protocol
     * @return bool
     */
    public function __invoke($name, ProtocolInterface $protocol)
    {
        $call = $this->rule;
        return !$call($name, $protocol);
    }
}
