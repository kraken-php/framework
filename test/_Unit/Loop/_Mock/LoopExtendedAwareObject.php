<?php

namespace Kraken\_Unit\Loop\_Mock;

use Kraken\Loop\LoopExtendedAwareInterface;
use Kraken\Loop\LoopExtendedAwareTrait;
use Kraken\Loop\LoopExtendedInterface;

class LoopExtendedAwareObject implements LoopExtendedAwareInterface
{
    use LoopExtendedAwareTrait;

    /**
     * @param LoopExtendedInterface|null $loop
     */
    public function __construct(LoopExtendedInterface $loop = null)
    {
        $this->loop = $loop;
    }
}
