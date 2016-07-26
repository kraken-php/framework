<?php

namespace Kraken\_Unit\Promise\Helper;

use Kraken\Promise\Helper\CancellationQueue;
use Kraken\Promise\Deferred;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\TUnit;
use Exception;

class CancellationQueueTest extends TUnit
{
    /**
     *
     */
    public function testApiEnqueue_AcceptsPromise()
    {
        $p = $this->createPromise();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $this->assertEquals(1, $cancellationQueue->getSize());
    }

    /**
     *
     */
    public function testApiEnqueue_DoesNotAcceptNonPromise()
    {
        $p = 'NonPromise';

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($p);

        $this->assertEquals(0, $cancellationQueue->getSize());
    }

    /**
     *
     */
    public function testApiGetSize_ReturnsSize()
    {
        $cancellationQueue = new CancellationQueue();
        $limit = 3;

        for ($i=0; $i<$limit; $i++)
        {
            $cancellationQueue->enqueue($this->createPromise());
        }

        $this->assertEquals($limit, $cancellationQueue->getSize());
    }

    /**
     *
     */
    public function testApiInvoke_CancelsPromises_EnqueuedBeforeStart()
    {
        $p1 = $this->createPromise();
        $p2 = $this->createPromise();

        $cancellationQueue = new CancellationQueue();

        $cancellationQueue->enqueue($p1);
        $cancellationQueue->enqueue($p2);

        $cancellationQueue();

        $this->assertTrue($p1->isCancelled());
        $this->assertTrue($p2->isCancelled());
    }

    /**
     *
     */
    public function testApiInvoke_CancelsPromises_EnqueuedAfterStart()
    {
        $p1 = $this->createPromise();
        $p2 = $this->createPromise();

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue();

        $cancellationQueue->enqueue($p1);
        $cancellationQueue->enqueue($p2);

        $this->assertTrue($p1->isCancelled());
        $this->assertTrue($p2->isCancelled());
    }

    /**
     *
     */
    public function testApiInvoke_ThrowsExceptionsFromCancel()
    {
        $this->setExpectedException(Exception::class, 'test');

        $mock = $this->getMock(PromiseInterface::class);
        $mock
            ->expects($this->once())
            ->method('cancel')
            ->will($this->throwException(new Exception('test')));

        $cancellationQueue = new CancellationQueue();
        $cancellationQueue->enqueue($mock);
        $cancellationQueue();
    }


    /**
     * @return PromiseInterface
     */
    private function createPromise()
    {
        return (new Deferred)->getPromise();
    }
}
