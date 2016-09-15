<?php

namespace Kraken\_Unit\Runtime\Supervision;

use Exception;
use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervision\Container\ContainerDestroy;

class ContainerDestroyTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = ContainerDestroy::class;

    /**
     *
     */
    public function testApisolver_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];

        $solver = $this->createSolver();
        $runtime = $this->createRuntime([ 'destroy' ]);
        $runtime
            ->expects($this->once())
            ->method('destroy');

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $solver, 'solver', [ $ex, $params ]
            )
        );
    }
}
