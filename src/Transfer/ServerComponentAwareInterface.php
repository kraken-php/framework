<?php

namespace Kraken\Transfer;

interface ServerComponentAwareInterface
{
    /**
     * Set component to which data is being transferred to.
     *
     * @param ServerComponentInterface|null $component
     */
    public function setComponent(ServerComponentInterface $component = null);

    /**
     * Get component to which data is being transferred to.
     *
     * @return ServerComponentInterface|null
     */
    public function getComponent();
}
