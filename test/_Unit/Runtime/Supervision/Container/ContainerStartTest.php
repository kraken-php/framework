<?php

namespace Kraken\_Unit\Runtime\Supervision;

use Exception;
use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervision\Container\ContainerStart;

class ContainerStartTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = ContainerStart::class;

    /**
     *
     */
    public function testApisolver_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];

        $solver = $this->createSolver();
        $runtime = $this->createRuntime([ 'start' ]);
        $runtime
            ->expects($this->once())
            ->method('start');

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $solver, 'solver', [ $ex, $params ]
            )
        );
    }
}
