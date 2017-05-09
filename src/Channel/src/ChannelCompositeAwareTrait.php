<?php

namespace Kraken\Channel;

trait ChannelCompositeAwareTrait
{
    /**
     * @var ChannelCompositeInterface|null
     */
    protected $channel = null;

    /**
     * @see ChannelCompositeAwareInterface::setChannel
     */
    public function setChannel(ChannelCompositeInterface $channel = null)
    {
        $this->channel = $channel;
    }

    /**
     * @see ChannelCompositeAwareInterface::getChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
