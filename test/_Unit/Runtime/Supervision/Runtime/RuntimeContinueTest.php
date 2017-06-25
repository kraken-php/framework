<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Supervision\Runtime\RuntimeContinue;
use Dazzle\Throwable\Exception\Logic\IllegalCallException;
use Exception;
use StdClass;

class RuntimeContinueTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = RuntimeContinue::class;

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
        $manager = $this->createChannel();
        $manager
            ->expects($this->once())
            ->method('send')
            ->with($origin, $this->isInstanceOf(RuntimeCommand::class))
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
    public function testApiSolve_ThrowsException_WhenParamOriginDoesNotExist()
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
