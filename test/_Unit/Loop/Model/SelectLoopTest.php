<?php

namespace Kraken\_Unit\Loop;

use Kraken\Loop\Model\SelectLoop;
use Kraken\Test\TUnit;

class SelectLoopTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createLoop();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $loop = $this->createLoop();
        unset($loop);
    }

    /**
     * @return SelectLoop
     */
    protected function createLoop()
    {
        return new SelectLoop();
    }
}
