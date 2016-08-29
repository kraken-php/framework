<?php

namespace Kraken\_Unit\Runtime\Supervisor;

use Exception;
use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervisor\Cmd\CmdDoNothing;

class CmdDoNothingTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = CmdDoNothing::class;

    /**
     *
     */
    public function testApiHandler_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];

        $solver = $this->createSolver();

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $solver, 'handler', [ $ex, $params ]
            )
        );
    }
}
