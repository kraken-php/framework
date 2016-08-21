<?php

namespace Kraken\Channel;

trait ChannelBaseAwareTrait
{
    /**
     * @var ChannelBaseInterface|null
     */
    protected $channel = null;

    /**
     * @see ChannelBaseAwareInterface::setChannel
     */
    public function setChannel(ChannelBaseInterface $channel = null)
    {
        $this->channel = $channel;
    }

    /**
     * @see ChannelBaseAwareInterface::getChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
