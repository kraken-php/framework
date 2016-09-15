<?php

namespace Kraken\_Unit\Runtime\Supervision;

use Exception;
use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervision\Container\ContainerStop;

class ContainerStopTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = ContainerStop::class;

    /**
     *
     */
    public function testApisolver_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];

        $solver = $this->createSolver();
        $runtime = $this->createRuntime([ 'stop' ]);
        $runtime
            ->expects($this->once())
            ->method('stop');

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $solver, 'solver', [ $ex, $params ]
            )
        );
    }
}
