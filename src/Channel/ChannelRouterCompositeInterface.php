<?php

namespace Kraken\Channel;

use Kraken\Throwable\Resource\ResourceUndefinedException;

interface ChannelRouterCompositeInterface extends ChannelRouterBaseInterface
{
    /**
     * @param string $name
     * @return ChannelRouterBaseInterface|ChannelRouterCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function bus($name);

    /**
     * @param string $name
     * @param ChannelRouterBaseInterface|ChannelRouterCompositeInterface $router
     * @return ChannelCompositeInterface
     */
    public function setBus($name, $router);

    /**
     * @param string $name
     * @return ChannelCompositeInterface
     */
    public function removeBus($name);

    /**
     * @return ChannelRouterBaseInterface[]|ChannelRouterCompositeInterface[]
     */
    public function getAllBuses();
}
