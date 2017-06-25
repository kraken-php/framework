<?php

namespace Kraken\_Unit\Runtime\Container\Manager;

use Kraken\Runtime\Container\Manager\ProcessManagerNull;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Test\TUnit;
use Dazzle\Throwable\Exception\Runtime\RejectionException;

class ProcessManagerNullTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $manager = $this->createProcessManager();

        $this->assertInstanceOf(ProcessManagerNull::class, $manager);
        $this->assertInstanceOf(ProcessManagerInterface::class, $manager);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $manager = $this->createProcessManager();
        unset($manager);
    }

    /**
     *
     */
    public function testApiExistsProcess_ReturnsFalse()
    {
        $manager = $this->createProcessManager();
        $this->assertFalse($manager->existsProcess('alias'));
    }

    /**
     *
     */
    public function testApiCreateProcess_RejectsPromise()
    {
        $manager = $this->createProcessManager();
        $alias = 'alias';
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->createProcess($alias, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyProcess_ResolvesPromise()
    {
        $manager = $this->createProcessManager();
        $alias = 'alias';
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->destroyProcess($alias, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartProcess_RejectsPromise()
    {
        $manager = $this->createProcessManager();
        $alias = 'alias';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->startProcess($alias)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopProcess_RejectsPromise()
    {
        $manager = $this->createProcessManager();
        $alias = 'alias';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->stopProcess($alias)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiCreateProcesses_RejectsPromise()
    {
        $manager = $this->createProcessManager();
        $aliases = [ 'alias1', 'alias2' ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->createProcesses($aliases, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyProcesses_ResolvesPromise()
    {
        $manager = $this->createProcessManager();
        $aliases = [ 'alias1', 'alias2' ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->destroyProcesses($aliases, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartProcesses_RejectsPromise()
    {
        $manager = $this->createProcessManager();
        $aliases = [ 'alias1', 'alias2' ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->stopProcesses($aliases)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopProcesses_RejectsPromise()
    {
        $manager = $this->createProcessManager();
        $aliases = [ 'alias1', 'alias2' ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->stopProcesses($aliases)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiGetProcesses_ResolvesPromiseWithEmptyArray()
    {
        $manager = $this->createProcessManager();

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with([]);

        $manager
            ->getProcesses()
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiFlushProcesses_ResolvesPromise()
    {
        $manager = $this->createProcessManager();

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->flushProcesses()
            ->then(
                $callable
            );
    }

    /**
     * @return ProcessManagerNull|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createProcessManager()
    {
        return $this->getMock(ProcessManagerNull::class, null);
    }
}
