<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Supervisor\Runtime\RuntimeStart;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
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
    public function testApiHandler_InvokesProperAction()
    {
        $ex = new Exception();

        $origin  = 'origin';
        $result = new StdClass;

        $solver  = $this->createSolver();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('startRuntime')
            ->with($origin)
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $solver, 'handler', [ $ex, [ 'origin' => $origin ] ]
            )
        );
    }

    /**
     *
     */
    public function testApiHandle_ThrowsException_WhenParamOriginDoesNotExist()
    {
        $solver = $this->createSolver();

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(IllegalCallException::class));

        $solver
            ->handle(new Exception, [])
            ->then(
                null,
                $callable
            );
    }
}
