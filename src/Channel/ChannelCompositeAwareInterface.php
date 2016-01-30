<?php

namespace Kraken\Channel;

interface ChannelCompositeAwareInterface
{
    /**
     * @param ChannelCompositeInterface|null $channel
     * @return ChannelCompositeInterface
     */
    public function setChannel(ChannelCompositeInterface $channel = null);

    /**
     * @return ChannelCompositeInterface
     */
    public function getChannel();

    /**
     * @return ChannelCompositeInterface
     */
    public function channel();
}
