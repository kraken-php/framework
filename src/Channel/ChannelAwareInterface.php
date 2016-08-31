<?php

namespace Kraken\Channel;

interface ChannelAwareInterface
{
    /**
     * Set the Channel of which object is aware of or null to remove it.
     *
     * @param ChannelInterface|null $channel
     * @return ChannelInterface
     */
    public function setChannel(ChannelInterface $channel = null);

    /**
     * Return ChannelComposite of which object is aware of or null if it does not exist.
     *
     * @return ChannelInterface|null
     */
    public function getChannel();
}
