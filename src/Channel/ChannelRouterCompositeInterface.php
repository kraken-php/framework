<?php

namespace Kraken\Channel;

use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;

interface ChannelRouterCompositeInterface extends ChannelRouterBaseInterface
{
    /**
     * Check if bus exists in router domain.
     *
     * @param string $name
     * @return bool
     */
    public function existsBus($name);

    /**
     * Return bus from router domain or throw exception if it does not exist.
     *
     * Throws ResourceUndefinedException if bus is not found.
     *
     * @param string $name
     * @return ChannelRouterBaseInterface|ChannelRouterCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function bus($name);

    /**
     * Add or replace existing bus in router domain.
     *
     * @param string $name
     * @param ChannelRouterBaseInterface|ChannelRouterCompositeInterface $router
     * @return ChannelCompositeInterface
     */
    public function setBus($name, $router);

    /**
     * Remove bus from router domain if it does exist.
     *
     * @param string $name
     * @return ChannelCompositeInterface
     */
    public function removeBus($name);

    /**
     * Return all buses existing in router domain.
     *
     * @return ChannelRouterBaseInterface[]|ChannelRouterCompositeInterface[]
     */
    public function getBuses();
}
