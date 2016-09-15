<?php

namespace Kraken\_Unit\Runtime\Supervision;

use Exception;
use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervision\Cmd\CmdDoNothing;

class CmdDoNothingTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = CmdDoNothing::class;

    /**
     *
     */
    public function testApisolver_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];

        $solver = $this->createSolver();

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $solver, 'solver', [ $ex, $params ]
            )
        );
    }
}
