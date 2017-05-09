<?php

namespace Kraken\Network;

interface NetworkComponentAwareInterface
{
    /**
     * Set component to which data is being transferred to.
     *
     * @param NetworkComponentInterface|null $component
     */
    public function setComponent(NetworkComponentInterface $component = null);

    /**
     * Get component to which data is being transferred to.
     *
     * @return NetworkComponentInterface|null
     */
    public function getComponent();
}
