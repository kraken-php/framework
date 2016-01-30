<?php

namespace Kraken\Loop;

trait LoopExtendedAwareTrait
{
    /**
     * @var LoopExtendedInterface
     */
    protected $loop;

    /**
     * @param LoopExtendedInterface|null $loop
     */
    public function setLoop(LoopExtendedInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @return LoopExtendedInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return LoopExtendedInterface
     */
    public function loop()
    {
        return $this->loop;
    }
}
