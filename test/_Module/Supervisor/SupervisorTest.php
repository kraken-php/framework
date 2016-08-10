<?php

namespace Kraken\_Module\Supervisor;

use Kraken\_Module\Supervisor\_Solver\ExpectedSolver;
use Kraken\_Module\Supervisor\_Solver\UnexpectedSolver;
use Kraken\Supervisor\SolverFactory;
use Kraken\Supervisor\SolverFactoryInterface;
use Kraken\Supervisor\SolverInterface;
use Kraken\Supervisor\Supervisor;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Runtime\IoException;
use Exception;

class SupervisorTest extends TModule
{
    /**
     *
     */
    public function testCaseSupervisor_HandlesException_UsingFirstValidHandler()
    {
        $ex = new RejectionException();
        $params = [ 'param' => 'value' ];
        $result = null;

        $unexpected = new UnexpectedSolver();
        $expected = new ExpectedSolver();

        $factory = new SolverFactory();
        $factory
            ->define('Unexpected', function() use($unexpected) {
                return $unexpected;
            })
            ->define('Expected', function() use($expected) {
                return $expected;
            })
        ;
        $super = $this->createSupervisor(
            $factory,
            [],
            [
                IoException::class          => 'Unexpected',
                ExecutionException::class   => 'Expected',
                Exception::class            => 'Unexpected'
            ]
        );

        $super
            ->handle($ex, $params)
            ->then(
                function($value) use(&$result) {
                    $result = $value;
                }
            );

        $this->assertSame($expected, $result);
    }

    /**
     *
     */
    public function testCaseSupervisor_RejectsPromise_WhenNoValidExceptionFound()
    {
        $ex = new RejectionException();
        $params = [ 'param' => 'value' ];
        $result = null;

        $factory = new SolverFactory();
        $factory
            ->define('Unexpected', function() {
                return new UnexpectedSolver();
            })
        ;
        $super = $this->createSupervisor(
            $factory,
            [],
            [
                IoException::class  => 'Unexpected'
            ]
        );

        $super
            ->handle($ex, $params)
            ->then(
                null,
                function($ex) use(&$result) {
                    $result = $ex;
                }
            );

        $this->assertInstanceOf(ExecutionException::class, $result);
    }

    /**
     * @param SolverFactoryInterface $factory
     * @param mixed[] $params
     * @param SolverInterface[]|string[] $rules
     * @return Supervisor
     */
    public function createSupervisor($factory, $params = [], $rules = [])
    {
        return new Supervisor($factory, $params, $rules);
    }
}
