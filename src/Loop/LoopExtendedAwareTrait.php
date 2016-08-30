<?php

namespace Kraken\Loop;

trait LoopExtendedAwareTrait
{
    /**
     * @var LoopExtendedInterface|null
     */
    protected $loop = null;

    /**
     * @see LoopExtendedAwareInterface::setLoop
     */
    public function setLoop(LoopExtendedInterface $loop = null)
    {
        $this->loop = $loop;
    }

    /**
     * @see LoopExtendedAwareInterface::getLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }
}
