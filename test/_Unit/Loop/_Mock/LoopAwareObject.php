<?php

namespace Kraken\_Unit\Loop\_Mock;

use Kraken\Loop\LoopAwareInterface;
use Kraken\Loop\LoopAwareTrait;
use Kraken\Loop\LoopInterface;

class LoopAwareObject implements LoopAwareInterface
{
    use LoopAwareTrait;

    /**
     * @param LoopInterface|null $loop
     */
    public function __construct(LoopInterface $loop = null)
    {
        $this->loop = $loop;
    }
}
