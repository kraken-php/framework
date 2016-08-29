<?php

namespace Kraken\_Unit\Runtime\Supervisor;

use Exception;
use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervisor\Container\ContainerStop;

class ContainerStopTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = ContainerStop::class;

    /**
     *
     */
    public function testApiHandler_InvokesProperAction()
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
                $solver, 'handler', [ $ex, $params ]
            )
        );
    }
}
