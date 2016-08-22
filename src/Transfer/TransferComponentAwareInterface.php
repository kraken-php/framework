<?php

namespace Kraken\Transfer;

interface TransferComponentAwareInterface
{
    /**
     * Set component to which data is being transferred to.
     *
     * @param TransferComponentInterface|null $component
     */
    public function setComponent(TransferComponentInterface $component = null);

    /**
     * Get component to which data is being transferred to.
     *
     * @return TransferComponentInterface|null
     */
    public function getComponent();
}
