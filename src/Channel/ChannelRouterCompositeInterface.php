<?php

namespace Kraken\Channel;

use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;

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
     * @return bool
     */
    public function existsBus($name);

    /**
     * @param string $name
     * @return ChannelCompositeInterface
     */
    public function removeBus($name);

    /**
     * @return ChannelRouterBaseInterface[]|ChannelRouterCompositeInterface[]
     */
    public function getBuses();
}
