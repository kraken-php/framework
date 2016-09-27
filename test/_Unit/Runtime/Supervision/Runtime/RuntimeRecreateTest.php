<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\Supervision\Runtime\RuntimeRecreate;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Exception;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use StdClass;

class RuntimeRecreateTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = RuntimeRecreate::class;

    /**
     *
     */
    public function testApisolver_ThrowsException_WhenRuntimeDoesNotExist()
    {
        $ex = new Exception();

        $origin = 'origin';
        $hash   = 'hash';

        $solver  = $this->createSolver();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('existsThread')
            ->with($origin)
            ->will($this->returnValue(false));
        $manager
            ->expects($this->once())
            ->method('existsProcess')
            ->with($origin)
            ->will($this->returnValue(false));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $this
            ->callProtectedMethod($solver, 'solver', [ $ex, [ 'origin' => $origin ] ])
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApisolver_InvokesProperAction_WhenRuntimeDoesExistAsThread()
    {
        $ex = new Exception();

        $origin = 'origin';
        $hash   = 'hash';
        $result = new StdClass;

        $solver  = $this->createSolver();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('existsThread')
            ->with($origin)
            ->will($this->returnValue(true));
        $manager
            ->expects($this->any())
            ->method('existsProcess')
            ->with($origin)
            ->will($this->returnValue(false));
        $manager
            ->expects($this->once())
            ->method('createThread')
            ->with($origin, null, Runtime::CREATE_FORCE, [ 'hash' => $hash ])
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
    public function testApisolver_InvokesProperAction_WhenRuntimeDoesExistAsProcess()
    {
        $ex = new Exception();

        $origin = 'origin';
        $hash   = 'hash';
        $result = new StdClass;

        $solver  = $this->createSolver();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('existsThread')
            ->with($origin)
            ->will($this->returnValue(false));
        $manager
            ->expects($this->any())
            ->method('existsProcess')
            ->with($origin)
            ->will($this->returnValue(true));
        $manager
            ->expects($this->once())
            ->method('createProcess')
            ->with($origin, null, Runtime::CREATE_FORCE, [ 'hash' => $hash ])
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
