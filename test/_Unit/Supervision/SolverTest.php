<?php

namespace Kraken\_Unit\Supervision;

use Kraken\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Kraken\Test\TUnit;
use Exception;

class SolverTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $solver = $this->createSolver();

        $this->assertInstanceOf(Solver::class, $solver);
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

        $solver = $this->createSolver([], [ 'solve' ]);
        $solver
            ->expects($this->once())
            ->method('solve')
            ->with($ex, $params);

        $solver($ex, $params);
    }

    /**
     *
     */
    public function testApiHandle_ReturnsRejectedPromise_WhenRequiredParamIsNotPassed()
    {
        $ex = new Exception('Exception');
        $params = [ 'param1' => 'value1' ];

        $solver = $this->createSolver();

        $this->setProtectedProperty($solver, 'requires', [
            'param1' => 'value1',
            'param2' => 'value2'
        ]);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(IllegalCallException::class));

        $solver
            ->solve($ex, $params)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiHandle_CallsHandlerMethod()
    {
        $ex = new Exception('Exception');
        $params = [ 'param1' => 'value1' ];
        $result = 'result';

        $solver = $this->createSolver([], [ 'solver' ]);
        $solver
            ->expects($this->once())
            ->method('solver')
            ->with($ex, $params)
            ->will($this->returnValue($result));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($result);

        $solver
            ->solve($ex, $params)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiHandler_ThrowsException()
    {
        $solver = $this->createSolver();

        $this->setExpectedException(RejectionException::class);
        $this->callProtectedMethod($solver, 'solver', [ new Exception, [] ]);
    }

    /**
     * @param mixed[] $context
     * @param string[]|null $methods
     * @return Solver|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSolver($context = [], $methods = null)
    {
        return $this->getMock(Solver::class, $methods, [ $context ]);
    }
}
