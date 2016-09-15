<?php

namespace Kraken\_Unit\Runtime\Supervision;

use Exception;
use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervision\Container\ContainerContinue;

class ContainerContinueTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = ContainerContinue::class;

    /**
     *
     */
    public function testApisolver_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];

        $solver = $this->createSolver();
        $runtime = $this->createRuntime([ 'succeed' ]);
        $runtime
            ->expects($this->once())
            ->method('succeed');

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $solver, 'solver', [ $ex, $params ]
            )
        );
    }
}
