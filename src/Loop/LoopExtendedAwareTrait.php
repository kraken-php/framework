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
    public function setLoop(LoopExtendedInterface $loop)
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

    // TODO delete this
    public function loop()
    {
        return $this->loop;
    }
}
