<?php

namespace Kraken\_Unit\Runtime\Supervisor;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervisor\Cmd\CmdEscalateSupervisor;
use Exception;

class CmdEscalateSupervisorTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = CmdEscalateSupervisor::class;

    /**
     *
     */
    public function testApiHandler_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];

        $solver  = $this->createSolver();
        $runtime = $this->createRuntime([ 'fail' ]);
        $runtime
            ->expects($this->once())
            ->method('fail')
            ->with($ex, $params);

        $this->callProtectedMethod(
            $solver, 'handler', [ $ex, $params ]
        );
    }
}
