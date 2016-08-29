<?php

namespace Kraken\_Unit\Runtime\Command;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Supervisor\Runtime\RuntimeContinue;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
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
    public function testApiHandler_InvokesProperAction()
    {
        $ex = new Exception();

        $origin  = 'origin';
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
