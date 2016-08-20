<?php

namespace Kraken\Channel\Routing\Strategy;

use Kraken\Channel\ChannelCompositeInterface;
use Kraken\Channel\Routing\RoutingInterface;

class RoutingDefault implements RoutingInterface
{
    /**
     * @var ChannelCompositeInterface
     */
    protected $channel;

    /**
     * @var mixed[]
     */
    protected $params;

    /**
     * @param ChannelCompositeInterface $channel
     * @param mixed[] $params
     */
    public function __construct(ChannelCompositeInterface $channel, $params = [])
    {
        $this->channel = $channel;
        $this->params = $params;
    }
}
