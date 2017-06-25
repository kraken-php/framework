<?php

namespace Kraken\_Module\Supervision;

use Kraken\_Module\Supervision\_Solver\ExpectedSolver;
use Kraken\_Module\Supervision\_Solver\UnexpectedSolver;
use Kraken\Supervision\SolverFactory;
use Kraken\Supervision\SolverFactoryInterface;
use Kraken\Supervision\SolverInterface;
use Kraken\Supervision\Supervisor;
use Dazzle\Throwable\Exception\Runtime\RejectionException;
use Dazzle\Throwable\Exception\Runtime\ExecutionException;
use Dazzle\Throwable\Exception\Runtime\WriteException;
use Dazzle\Throwable\Exception\RuntimeException;
use Kraken\Test\TModule;
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
                WriteException::class       => 'Unexpected',
                RuntimeException::class     => 'Expected',
                Exception::class            => 'Unexpected'
            ]
        );

        $super
            ->solve($ex, $params)
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
                WriteException::class => 'Unexpected'
            ]
        );

        $super
            ->solve($ex, $params)
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
