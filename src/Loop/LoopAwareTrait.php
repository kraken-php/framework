<?php

namespace Kraken\Loop;

trait LoopAwareTrait
{
    /**
     * @var LoopInterface|null
     */
    protected $loop = null;

    /**
     * @see LoopAwareInterface::setLoop
     */
    public function setLoop(LoopInterface $loop = null)
    {
        $this->loop = $loop;
    }

    /**
     * @see LoopAwareInterface::getLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }
}
