<?php

namespace Kraken\_Unit\Runtime\Supervisor;

use Exception;
use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervisor\Container\ContainerStart;

class ContainerStartTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = ContainerStart::class;

    /**
     *
     */
    public function testApiHandler_InvokesProperAction()
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
                $solver, 'handler', [ $ex, $params ]
            )
        );
    }
}
