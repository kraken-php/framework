<?php

namespace Kraken\_Module\Loop;

use Kraken\Loop\Flow\FlowController;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopExtendedInterface;
use Kraken\Loop\LoopModelInterface;
use Kraken\Test\TModule;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LoopTest extends LoopModelTest
{
    /**
     * @return LoopExtendedInterface|LoopModelInterface
     */
    public function createLoop()
    {
        return new Loop(new SelectLoop());
    }
}
