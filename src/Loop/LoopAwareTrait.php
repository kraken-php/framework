<?php

namespace Kraken\Loop;

trait LoopAwareTrait
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @param LoopInterface|null $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return LoopInterface
     */
    public function loop()
    {
        return $this->loop;
    }
}
