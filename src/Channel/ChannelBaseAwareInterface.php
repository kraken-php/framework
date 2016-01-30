<?php

namespace Kraken\Channel;

interface ChannelBaseAwareInterface
{
    /**
     * @param ChannelBaseInterface|null $channel
     * @return ChannelBaseInterface
     */
    public function setChannel(ChannelBaseInterface $channel = null);

    /**
     * @return ChannelBaseInterface
     */
    public function getChannel();

    /**
     * @return ChannelBaseInterface
     */
    public function channel();
}
