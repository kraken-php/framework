<?php

namespace Kraken\Channel;

use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;

interface ChannelCompositeInterface extends ChannelBaseInterface
{
    /**
     * @param string $name
     * @return ChannelBaseInterface|ChannelCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function bus($name);

    /**
     * @param string $name
     * @param ChannelBaseInterface|ChannelCompositeInterface $channel
     * @return ChannelCompositeInterface
     */
    public function setBus($name, $channel);

    /**
     * @param string $name
     * @return ChannelCompositeInterface
     */
    public function removeBus($name);

    /**
     * @return ChannelBaseInterface[]|ChannelCompositeInterface[]
     */
    public function getAllBuses();
}
