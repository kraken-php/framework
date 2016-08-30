<?php

namespace Kraken\Channel;

use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;

interface ChannelCompositeInterface extends ChannelBaseInterface
{
    /**
     * Check if bus exists in channel domain.
     *
     * @param string $name
     * @return bool
     */
    public function existsBus($name);

    /**
     * Return bus from channel domain or throw exception if it does not exist.
     *
     * Throws ResourceUndefinedException if bus is not found.
     *
     * @param string $name
     * @return ChannelBaseInterface|ChannelCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function getBus($name);

    /**
     * Add or replace existing bus in channel domain.
     *
     * @param string $name
     * @param ChannelBaseInterface|ChannelCompositeInterface $channel
     * @return ChannelCompositeInterface
     */
    public function setBus($name, $channel);

    /**
     * Remove bus from channel domain if it does exist.
     *
     * @param string $name
     * @return ChannelCompositeInterface
     */
    public function removeBus($name);

    /**
     * Return all buses existing in channel domain.
     *
     * @return ChannelBaseInterface[]|ChannelCompositeInterface[]
     */
    public function getBuses();
}
