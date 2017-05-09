<?php

namespace Kraken\Channel;

trait ChannelAwareTrait
{
    /**
     * @var ChannelInterface|null
     */
    protected $channel = null;

    /**
     * @see ChannelAwareInterface::setChannel
     */
    public function setChannel(ChannelInterface $channel = null)
    {
        $this->channel = $channel;
    }

    /**
     * @see ChannelAwareInterface::getChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
