<?php

namespace Kraken\Channel\Router;

use Kraken\Channel\ChannelCompositeInterface;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;

interface RouterCompositeInterface extends RouterInterface
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
     * @return RouterInterface|RouterCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function getBus($name);

    /**
     * Add or replace existing bus in router domain.
     *
     * @param string $name
     * @param RouterInterface|RouterCompositeInterface $router
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
     * @return RouterInterface[]|RouterCompositeInterface[]
     */
    public function getBuses();
}
