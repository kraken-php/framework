<?php

namespace Kraken\Channel\Router;

use Kraken\Channel\ChannelProtocolInterface;

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
     * @param ChannelProtocolInterface $protocol
     * @return bool
     */
    public function __invoke($name, ChannelProtocolInterface $protocol)
    {
        return !call_user_func_array($this->rule, [ $name, $protocol ]);
    }
}
