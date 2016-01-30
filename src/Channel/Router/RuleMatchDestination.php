<?php

namespace Kraken\Channel\Router;

use Kraken\Channel\ChannelProtocolInterface;
use Kraken\Support\StringSupport;

class RuleMatchDestination
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->name);
    }

    /**
     * @param string $name
     * @param ChannelProtocolInterface $protocol
     * @return bool
     */
    public function __invoke($name, ChannelProtocolInterface $protocol)
    {
        return StringSupport::match($this->name, $protocol->getDestination());
    }
}
