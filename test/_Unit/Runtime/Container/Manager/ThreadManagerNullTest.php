<?php

namespace Kraken\_Unit\Runtime\Container\Manager;

use Kraken\Runtime\Container\Manager\ThreadManagerNull;
use Kraken\Runtime\Container\ThreadManagerInterface;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;

class ThreadManagerNullTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $manager = $this->createThreadManager();

        $this->assertInstanceOf(ThreadManagerNull::class, $manager);
        $this->assertInstanceOf(ThreadManagerInterface::class, $manager);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $manager = $this->createThreadManager();
        unset($manager);
    }

    /**
     *
     */
    public function testApiExistsThread_ReturnsFalse()
    {
        $manager = $this->createThreadManager();
        $this->assertFalse($manager->existsThread('alias'));
    }

    /**
     *
     */
    public function testApiCreateThread_RejectsPromise()
    {
        $manager = $this->createThreadManager();
        $alias = 'alias';
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->createThread($alias, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyThread_ResolvesPromise()
    {
        $manager = $this->createThreadManager();
        $alias = 'alias';
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->destroyThread($alias, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartThread_RejectsPromise()
    {
        $manager = $this->createThreadManager();
        $alias = 'alias';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->startThread($alias)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopThread_RejectsPromise()
    {
        $manager = $this->createThreadManager();
        $alias = 'alias';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->stopThread($alias)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiCreateThreads_RejectsPromise()
    {
        $manager = $this->createThreadManager();
        $aliases = [ 'alias1', 'alias2' ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->createThreads($aliases, $flags)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiDestroyThreads_ResolvesPromise()
    {
        $manager = $this->createThreadManager();
        $aliases = [ 'alias1', 'alias2' ];
        $flags = 'flags';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->destroyThreads($aliases, $flags)
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiStartThreads_RejectsPromise()
    {
        $manager = $this->createThreadManager();
        $aliases = [ 'alias1', 'alias2' ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->stopThreads($aliases)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiStopThreads_RejectsPromise()
    {
        $manager = $this->createThreadManager();
        $aliases = [ 'alias1', 'alias2' ];

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(RejectionException::class));

        $manager
            ->stopThreads($aliases)
            ->then(
                null,
                $callable
            );
    }

    /**
     *
     */
    public function testApiGetThreads_ResolvesPromiseWithEmptyArray()
    {
        $manager = $this->createThreadManager();

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with([]);

        $manager
            ->getThreads()
            ->then(
                $callable
            );
    }

    /**
     *
     */
    public function testApiFlushThreads_ResolvesPromise()
    {
        $manager = $this->createThreadManager();

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isType('string'));

        $manager
            ->flushThreads()
            ->then(
                $callable
            );
    }
    /**
     * @return ThreadManagerNull
     */
    public function createThreadManager()
    {
        return new ThreadManagerNull();
    }
}
