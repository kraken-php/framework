<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\Supervisor\Runtime\RuntimeRecreate;
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
    public function testApiHandler_ThrowsException_WhenRuntimeDoesNotExist()
    {
        $ex = new Exception();

        $origin  = 'origin';

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
            ->callProtectedMethod($solver, 'handler', [ $ex, [ 'origin' => $origin ] ])
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiHandler_InvokesProperAction_WhenRuntimeDoesExistAsThread()
    {
        $ex = new Exception();

        $origin  = 'origin';
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
            ->with($origin, null, Runtime::CREATE_FORCE)
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
    public function testApiHandler_InvokesProperAction_WhenRuntimeDoesExistAsProcess()
    {
        $ex = new Exception();

        $origin  = 'origin';
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
            ->with($origin, null, Runtime::CREATE_FORCE)
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
