<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervision\Runtime\RuntimeStart;
use Dazzle\Throwable\Exception\Logic\IllegalCallException;
use Exception;
use StdClass;

class RuntimeStartTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = RuntimeStart::class;

    /**
     *
     */
    public function testApisolver_InvokesProperAction()
    {
        $ex = new Exception();

        $origin = 'origin';
        $hash   = 'hash';
        $result = new StdClass;

        $solver  = $this->createSolver();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('startRuntime')
            ->with($origin, [ 'hash' => $hash ])
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $solver, 'solver', [ $ex, [ 'origin' => $origin, 'hash' => $hash ] ]
            )
        );
    }

    /**
     *
     */
    public function testApisolve_ThrowsException_WhenParamOriginDoesNotExist()
    {
        $solver = $this->createSolver();

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(IllegalCallException::class));

        $solver
            ->solve(new Exception, [])
            ->then(
                null,
                $callable
            );
    }
}
