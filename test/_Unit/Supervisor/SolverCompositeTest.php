<?php

namespace Kraken\_Unit\Supervisor;

use Kraken\Promise\Promise;
use Kraken\Supervisor\SolverBase;
use Kraken\Supervisor\SolverComposite;
use Kraken\Supervisor\SolverInterface;
use Kraken\Test\TUnit;
use Exception;

class SolverCompositeTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $solver = $this->createSolver();

        $this->assertInstanceOf(SolverComposite::class, $solver);
        $this->assertInstanceOf(SolverInterface::class, $solver);
    }

    /**
     *
     */
    public function testApiConstructor_CallsConstructMethod()
    {
        $solver = $this->createSolver([], [ 'construct' ]);
        $solver
            ->expects($this->once())
            ->method('construct');

        $solver->__construct();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $solver = $this->createSolver();
        unset($solver);
    }

    /**
     *
     */
    public function testApiDestructor_CallsDestructMethod()
    {
        $solver = $this->createSolver([], [ 'destruct' ]);
        $solver
            ->expects($this->once())
            ->method('destruct');

        $solver->__destruct();
    }

    /**
     *
     */
    public function testApiInvoke_CallsHandleMethod()
    {
        $ex = new Exception('Exception');
        $params = [ 'param1' => 'value1', 'param2' => 'value2' ];

        $solver = $this->createSolver([], [ 'handle' ]);
        $solver
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $params);

        $solver($ex, $params);
    }

    /**
     *
     */
    public function testApiHandle_CallsHandlerMethod()
    {
        $ex = new Exception('Exception');
        $params = [ 'param1' => 'value1' ];
        $result = 'result';

        $solver = $this->createSolver([], [ 'handler' ]);
        $solver
            ->expects($this->once())
            ->method('handler')
            ->with($ex, $params)
            ->will($this->returnValue($result));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($result);

        $solver
            ->handle($ex, $params)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiHandler_CallsHandlersOneAfterAnother()
    {
        $ex = new Exception('Exception');
        $params = [ 'params1' => 'value1' ];
        $queue = '';

        $solver1 = $this->getMock(SolverInterface::class, [], [], '', false);
        $solver1
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $params)
            ->will($this->returnCallback(function() use(&$queue) {
                $queue .= 'A';
                return Promise::doResolve()->then(function() use(&$queue) {
                    $queue .= 'C';
                });
            }));

        $solver2 = $this->getMock(SolverInterface::class, [], [], '', false);
        $solver2
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $params)
            ->will($this->returnCallback(function() use(&$queue) {
                $queue .= 'B';
                return 'done';
            }));

        $solver = $this->createSolver([
            $solver1,
            $solver2
        ]);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with('done');

        $result = $this->callProtectedMethod($solver, 'handler', [ $ex, $params ]);
        $result
            ->then($callable);

        $this->assertSame('ACB', $queue);
    }

    /**
     *
     */
    public function testApiHandler_RejectsPromise_OnFirstRejection()
    {
        $ex1 = new Exception('Exception');
        $ex2 = new Exception('Other Exception');
        $params = [ 'params1' => 'value1' ];
        $queue = '';

        $solver1 = $this->getMock(SolverInterface::class, [], [], '', false);
        $solver1
            ->expects($this->once())
            ->method('handle')
            ->with($ex1, $params)
            ->will($this->returnCallback(function() use(&$queue, $ex2) {
                $queue .= 'A';
                return Promise::doReject($ex2);
            }));

        $solver2 = $this->getMock(SolverInterface::class, [], [], '', false);
        $solver2
            ->expects($this->never())
            ->method('handle')
            ->with($ex1, $params)
            ->will($this->returnCallback(function() use(&$queue) {
                $queue .= 'B';
            }));

        $solver3 = $this->getMock(SolverInterface::class, [], [], '', false);
        $solver3
            ->expects($this->never())
            ->method('handle')
            ->with($ex1, $params)
            ->will($this->returnCallback(function() use(&$queue) {
                $queue .= 'C';
            }));

        $solver = $this->createSolver([
            $solver1,
            $solver2,
            $solver3
        ]);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($ex2);

        $result = $this->callProtectedMethod($solver, 'handler', [ $ex1, $params ]);
        $result
            ->then(null, $callable);

        $this->assertSame('A', $queue);
    }

    /**
     * @param SolverInterface[] $handlers
     * @param string[]|null $methods
     * @return SolverBase|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSolver($handlers = [], $methods = null)
    {
        return $this->getMock(SolverComposite::class, $methods, [ $handlers ]);
    }
}
