<?php

namespace Kraken\_Unit\Runtime\_Mock;

use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\Container\ThreadManagerInterface;
use Kraken\Runtime\RuntimeManager;

class RuntimeManagerMock extends RuntimeManager
{
    /**
     * @return ThreadManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getThread()
    {
        return null;
    }

    /**
     * @return ProcessManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getProcess()
    {
        return null;
    }
}
