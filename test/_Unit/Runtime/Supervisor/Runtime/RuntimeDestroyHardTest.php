<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\Supervisor\Runtime\RuntimeDestroyHard;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Exception;
use StdClass;

class RuntimeDestroyHardTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = RuntimeDestroyHard::class;

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
            ->method('destroyRuntime')
            ->with($origin, Runtime::DESTROY_FORCE_HARD)
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
