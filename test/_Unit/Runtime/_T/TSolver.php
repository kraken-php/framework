<?php

namespace Kraken\_Unit\Runtime\_T;

use Kraken\Channel\Channel;
use Kraken\Channel\ChannelInterface;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Core\Core;
use Kraken\Log\Logger;
use Kraken\Runtime\RuntimeContainer;
use Kraken\Runtime\RuntimeManagerInterface;
use Kraken\Runtime\Supervision\Solver;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Supervision\SolverInterface;
use Dazzle\Throwable\Exception\Logic\InstantiationException;
use Kraken\Test\TUnit;
use Exception;

class TSolver extends TUnit
{
    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var CommandInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $solver;

    /**
     * @var RuntimeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var RuntimeContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $runtime;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->solver  = null;
        $this->runtime = null;
    }

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
    public function testApiConstructor_SetsRuntime()
    {
        $solver  = $this->createSolver();
        $runtime = $this->getProtectedProperty($solver, 'runtime');

        $this->assertInstanceOf(RuntimeContainerInterface::class, $runtime);
    }

    /**
     *
     */
    public function testApiConstructor_ThrowsException_WhenNoRuntimeIsPassed()
    {
        $this->setExpectedException(InstantiationException::class);
        $this->createSolver([ 'runtime' => null ]);
    }

    /**
     *
     */
    public function testApiConstruct_DoesNotThrowException()
    {
        $solver = $this->createSolver();

        $this->callProtectedMethod($solver, 'construct');
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
    public function testApiDestruct_DoesNotThrowException()
    {
        $solver = $this->createSolver();

        $this->callProtectedMethod($solver, 'destruct');
    }

    /**
     * @param string[]|null $methods
     * @return ChannelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createLogger($methods = [])
    {
        $logger = $this->getMock(Logger::class, $methods, [], '', false);

        if ($this->solver !== null && $this->existsProtectedProperty($this->solver, 'logger'))
        {
            $this->setProtectedProperty($this->solver, 'logger', $logger);
        }

        return $logger;
    }

    /**
     * @param string[]|null $methods
     * @return ChannelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createChannel($methods = [])
    {
        $channel = $this->getMock(Channel::class, $methods, [], '', false);

        if ($this->solver !== null && $this->existsProtectedProperty($this->solver, 'channel'))
        {
            $this->setProtectedProperty($this->solver, 'channel', $channel);
        }

        return $channel;
    }

    /**
     * @return RuntimeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createManager()
    {
        if ($this->solver !== null && $this->existsProtectedProperty($this->solver, 'manager'))
        {
            $this->setProtectedProperty($this->solver, 'manager', $this->manager);
        }

        return $this->manager;
    }

    /**
     * @param string[]|null $methods
     * @return RuntimeContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRuntime($methods = [])
    {
        $methods = array_merge($methods, [
            'getManager',
            'getCore',
            'getParent'
        ]);

        $manager = $this->getMock(RuntimeManagerInterface::class, [], [], '', false);
        $core    = $this->getMock(Core::class, [ 'make' ], []);
        $core
            ->expects($this->any())
            ->method('make')
            ->will($this->returnValue(null));

        $runtime = $this->getMock(RuntimeContainer::class, $methods, [ 'parent', 'alias', 'name' ]);
        $runtime
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($manager));
        $runtime
            ->expects($this->any())
            ->method('getCore')
            ->will($this->returnValue($core));
        $runtime
            ->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue('parent'));

        if ($this->solver !== null)
        {
            $this->setProtectedProperty($this->solver, 'runtime', $runtime);
        }

        $this->manager = $manager;
        $this->runtime = $runtime;
        $this->core    = $core;

        return $runtime;
    }

    /**
     * @param mixed[] $context
     * @param string[]|null $methods
     * @return SolverInterface|\PHPUnit_Framework_MockObject_MockObject
     * @throws Exception
     */
    public function createSolver($context = [], $methods = null)
    {
        if ($this->class === '')
        {
            throw new Exception('Class not set');
        }

        if (!array_key_exists('runtime', $context))
        {
            $context['runtime'] = $this->createRuntime();
        }

        $this->solver = $this->getMock($this->class, $methods, [ $context ]);

        return $this->solver;
    }
}
