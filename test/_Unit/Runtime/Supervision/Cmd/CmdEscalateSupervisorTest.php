<?php

namespace Kraken\_Unit\Runtime\Supervision;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervision\Cmd\CmdEscalateSupervisor;
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
    public function testApisolver_InvokesProperAction()
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
            $solver, 'solver', [ $ex, $params ]
        );
    }
}
