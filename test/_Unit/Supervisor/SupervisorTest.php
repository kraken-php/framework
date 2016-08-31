<?php

namespace Kraken\_Unit\Supervisor;

use Kraken\Supervisor\Solver;
use Kraken\Supervisor\SolverComposite;
use Kraken\Supervisor\SolverFactory;
use Kraken\Supervisor\SolverInterface;
use Kraken\Supervisor\Supervisor;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Runtime\WriteException;
use Kraken\Throwable\Exception\RuntimeException;
use Kraken\Test\TUnit;
use Exception;

class SupervisorTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $params = [
            'param1' => 'value1',
            'param2' => 'value2'
        ];
        $rules = [
            $rule1 = $this->createSolver(),
            $rule2 = $this->createSolver()
        ];

        $super = $this->createSupervisor($params, $rules);

        $paramsResults = $this->getProtectedProperty($super, 'params');
        $rulesResults  = $this->getProtectedProperty($super, 'rules');

        $this->assertSame($params, $paramsResults);
        $this->assertSame($rules, $rulesResults);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $super = $this->createSupervisor();
        unset($super);
    }

    /**
     *
     */
    public function testApiInvoke_CallsHandleMethod()
    {
        $ex = new Exception('Exception');
        $params = [ 'param1' => 'value1', 'param2' => 'value2' ];
        $result = 'result';

        $super = $this->createSupervisor([], [], [ 'handle' ]);
        $super
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $params)
            ->will($this->returnValue($result));

        $this->assertSame($result, $super($ex, $params));
    }

    /**
     *
     */
    public function testApiExistsParam_ReturnsFalse_WhenParamDoesNotExist()
    {
        $super = $this->createSupervisor();

        $this->assertFalse($super->existsParam('param'));
    }

    /**
     *
     */
    public function testApiExistsParam_ReturnsTrue_WhenParamDoesExist()
    {
        $super = $this->createSupervisor([ 'param' => 'value' ]);

        $this->assertTrue($super->existsParam('param'));
    }

    /**
     *
     */
    public function testApiSetParam_SetsParam()
    {
        $super = $this->createSupervisor();

        $this->assertSame(null, $super->getParam('param'));
        $super->setParam('param', 'value');
        $this->assertSame('value', $super->getParam('param'));
    }

    /**
     *
     */
    public function testApiGetParam_ReturnsNull_WhenParamDoesNotExist()
    {
        $super = $this->createSupervisor();

        $this->assertSame(null, $super->getParam('param'));
    }

    /**
     *
     */
    public function testApiGetParam_ReturnsParam_WhenParamDoesExist()
    {
        $super = $this->createSupervisor([ 'param' => 'value' ]);

        $this->assertSame('value', $super->getParam('param'));
    }

    /**
     *
     */
    public function testApiRemoveParam_RemovesParam_WhenParamDoesExist()
    {
        $super = $this->createSupervisor([ 'param' => 'value' ]);

        $this->assertTrue($super->existsParam('param'));
        $super->removeParam('param');
        $this->assertFalse($super->existsParam('param'));
    }

    /**
     *
     */
    public function testApiRemoveParam_DoesNothing_WhenParamDoesNotExist()
    {
        $super = $this->createSupervisor();

        $this->assertFalse($super->existsParam('param'));
        $super->removeParam('param');
        $this->assertFalse($super->existsParam('param'));
    }

    /**
     *
     */
    public function testApiExistsHandler_ReturnsFalse_WhenHandlerDoesNotExist()
    {
        $super = $this->createSupervisor();

        $this->assertFalse($super->existsHandler(Exception::class));
    }

    /**
     *
     */
    public function testApiExistsHandler_ReturnsTrue_WhenHandlerDoesExist()
    {
        $super = $this->createSupervisor([], [
            Exception::class => $this->createSolver()
        ]);

        $this->assertTrue($super->existsHandler(Exception::class));
    }

    /**
     *
     */
    public function testApiSetHandler_ResolvesHandler_WhenPassedNameOfExistingHandler()
    {
        $super = $this->createSupervisor();

        $super->setHandler(Exception::class, 'TestHandler');
        $result = $super->getHandler(Exception::class);

        $this->assertInstanceOf(SolverInterface::class, $result);
    }

    /**
     *
     */
    public function testApiSetHandler_AcceptsHandler_WhenConcreteHandlerIsPassed()
    {
        $super = $this->createSupervisor();

        $super->setHandler(Exception::class, $solver = $this->createSolver());
        $result = $super->getHandler(Exception::class);

        $this->assertSame($solver, $result);
    }

    /**
     *
     */
    public function testApiSetHandler_ResolvesHandler_WhenPassedMultipleNamesOfExistingHandlers()
    {
        $super = $this->createSupervisor();

        $super->setHandler(Exception::class, [ 'TestHandler', 'ValidHandler' ]);
        $result = $super->getHandler(Exception::class);
        $handlers = $this->getProtectedProperty($result, 'handlers');

        $this->assertInstanceOf(SolverComposite::class, $result);
        $this->assertCount(2, $handlers);
    }

    /**
     *
     */
    public function testApiSetHandler_ResolvesHandler_WhenPassedMixedArgumentsArray()
    {
        $super = $this->createSupervisor();

        $super->setHandler(Exception::class, [ 'TestHandler', $this->createSolver() ]);
        $result = $super->getHandler(Exception::class);
        $handlers = $this->getProtectedProperty($result, 'handlers');

        $this->assertInstanceOf(SolverComposite::class, $result);
        $this->assertCount(2, $handlers);
    }

    /**
     *
     */
    public function testApiSetHandler_ThrowsException_WhenAtLeastOneHandlerIsInvalid()
    {
        $super = $this->createSupervisor();

        $this->setExpectedException(IllegalCallException::class);
        $super->setHandler(Exception::class, [ 'TestHandler', 'OtherHandler' ]);
    }

    /**
     *
     */
    public function testApiGetHandler_ReturnsNull_WhenHandlerDoesNotExist()
    {
        $super = $this->createSupervisor();

        $this->assertSame(null, $super->getHandler(Exception::class));
    }

    /**
     *
     */
    public function testApiGetHandler_ReturnsHandler_WhenHandlerDoesExist()
    {
        $super = $this->createSupervisor([], [
            Exception::class => $solver = $this->createSolver()
        ]);

        $this->assertSame($solver, $super->getHandler(Exception::class));
    }

    /**
     *
     */
    public function testApiRemoveHandler_RemovesHandler_WhenHandlerDoesExist()
    {
        $super = $this->createSupervisor([], [
            Exception::class => $solver = $this->createSolver()
        ]);

        $this->assertTrue($super->existsHandler(Exception::class));
        $super->removeHandler(Exception::class);
        $this->assertFalse($super->existsHandler(Exception::class));
    }

    /**
     *
     */
    public function testApiRemoveHandler_DoesNothing_WhenHandlerDoesNotExist()
    {
        $super = $this->createSupervisor();

        $this->assertFalse($super->existsHandler(Exception::class));
        $super->removeHandler(Exception::class);
        $this->assertFalse($super->existsHandler(Exception::class));
    }

    /**
     *
     */
    public function testApiHandle_HandlesException_UsingFirstValidHandler()
    {
        $ex = new RejectionException();
        $params = [ 'param' => 'value' ];
        $result = 'result';

        $super = $this->createSupervisor();

        $expected = $this->createSolver([ 'handle' ]);
        $expected
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $params)
            ->will($this->returnValue($result));

        $unexpected = $this->createSolver([ 'handle' ]);
        $unexpected
            ->expects($this->never())
            ->method('handle');

        $super->setHandler(WriteException::class, $unexpected);
        $super->setHandler(RuntimeException::class, $expected);
        $super->setHandler(Exception::class, $unexpected);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($result);

        $super
            ->handle($ex, $params)
            ->then($callable);
    }

    /**
     *
     */
    public function testApiHandle_RejectsPromise_WhenNoValidExceptionFound()
    {
        $ex = new RejectionException();
        $params = [ 'param' => 'value' ];

        $super = $this->createSupervisor();

        $unexpected = $this->createSolver([ 'handle' ]);
        $unexpected
            ->expects($this->never())
            ->method('handle');

        $super->setHandler(WriteException::class, $unexpected);

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ExecutionException::class));

        $super
            ->handle($ex, $params)
            ->then(null, $callable);
    }

    /**
     *
     */
    public function testApiResolveHandler_ReturnsImmediately_WhenConcreteHandlerPassed()
    {
        $super = $this->createSupervisor();

        $result = $this->callProtectedMethod($super, 'resolveHandler', [ 'TestHandler' ]);

        $this->assertInstanceOf(SolverInterface::class, $result);
    }

    /**
     *
     */
    public function testApiResolveHandler_ResolvesHandler_WhenHandlerDoesExistInsideFactory()
    {
        $super = $this->createSupervisor();

        $result = $this->callProtectedMethod($super, 'resolveHandler', [ 'TestHandler' ]);

        $this->assertInstanceOf(SolverInterface::class, $result);
    }

    /**
     *
     */
    public function testApiResolveHandler_ThrowsException_WhenHandlerDoesNotExistInsideFactory()
    {
        $super = $this->createSupervisor();

        $this->setExpectedException(IllegalCallException::class);
        $this->callProtectedMethod($super, 'resolveHandler', [ 'OtherHandler' ]);
    }

    /**
     * @param string[]|null $methods
     * @return SolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSolver($methods = null)
    {
        return $this->getMock(Solver::class, $methods, [], '', false);
    }

    /**
     * @param mixed[] $params
     * @param SolverInterface[] $rules
     * @param mixed[]|null $methods
     * @return Supervisor|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSupervisor($params = [], $rules = [], $methods = null)
    {
        $factory = new SolverFactory();
        $factory
            ->define('TestHandler', function() {
                return $this->createSolver();
            })
            ->define('ValidHandler', function() {
                return $this->createSolver();
            })
        ;
        return $this->getMock(Supervisor::class, $methods, [ $factory, $params, $rules ]);
    }
}
