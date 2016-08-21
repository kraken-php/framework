<?php

namespace Kraken\Channel;

interface ChannelBaseAwareInterface
{
    /**
     * Set the ChannelBase of which object is aware of or null to remove it.
     *
     * @param ChannelBaseInterface|null $channel
     * @return ChannelBaseInterface
     */
    public function setChannel(ChannelBaseInterface $channel = null);

    /**
     * Return ChannelComposite of which object is aware of or null if it does not exist.
     *
     * @return ChannelBaseInterface|null
     */
    public function getChannel();
}
