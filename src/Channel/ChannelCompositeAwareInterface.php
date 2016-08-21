<?php

namespace Kraken\Channel;

interface ChannelCompositeAwareInterface
{
    /**
     * Set the ChannelComposite of which object is aware of or null to remove it.
     *
     * @param ChannelCompositeInterface|null $channel
     * @return ChannelCompositeInterface
     */
    public function setChannel(ChannelCompositeInterface $channel = null);

    /**
     * Return ChannelComposite of which object is aware of or null if it does not exist.
     *
     * @return ChannelCompositeInterface|null
     */
    public function getChannel();
}
