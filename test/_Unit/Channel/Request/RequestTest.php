<?php

namespace Kraken\_Unit\Channel\Request;

use Exception;
use Kraken\Channel\Request\Request;
use Kraken\Test\TUnit;
use stdClass;

class RequestTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createRequest('pid');
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $req = $this->createRequest('pid');
        unset($req);
    }

    /**
     *
     */
    public function testApiPid_ReturnsPid()
    {
        $req = $this->createRequest('pid');
        $this->assertSame('pid', $req->pid());
    }

    /**
     *
     */
    public function testApiOnSuccess_ReturnsOnSuccessHandler()
    {
        $callable = function() {};
        $req = $this->createRequest('pid', $callable);
        $this->assertSame($callable, $req->onSuccess());
    }

    /**
     *
     */
    public function testApiOnFailure_ReturnsOnFailureHandler()
    {
        $callable = function() {};
        $req = $this->createRequest('pid', null, $callable);
        $this->assertSame($callable, $req->onFailure());
    }

    /**
     *
     */
    public function testApiOnCancel_ReturnsOnCancelHandler()
    {
        $callable = function() {};
        $req = $this->createRequest('pid', null, null, $callable);
        $this->assertSame($callable, $req->onCancel());
    }

    /**
     *
     */
    public function testApiTimeout_ReturnsTimeout()
    {
        $timeout = 1.0;
        $req = $this->createRequest('pid', null, null, null, $timeout);
        $this->assertSame($timeout, $req->timeout());
    }

    /**
     *
     */
    public function testApiResolve_InvokesOnSuccessHandler()
    {
        $value  = new StdClass;
        $result = new StdClass;
        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($value)
            ->will($this->returnValue($result));

        $req = $this->createRequest('pid', $callable);

        $this->assertSame($result, $req->resolve($value));
    }

    /**
     *
     */
    public function testApiReject_InvokesOnFailureHandler()
    {
        $expected = new Exception;
        $result = new StdClass;
        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($expected)
            ->will($this->returnValue($result));

        $req = $this->createRequest('pid', null, $callable);

        $this->assertSame($result, $req->reject($expected));
    }

    /**
     *
     */
    public function testApiCancel_InvokesOnAbortHandler()
    {
        $expected = new Exception;
        $result = new StdClass;
        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($expected)
            ->will($this->returnValue($result));

        $req = $this->createRequest('pid', null, null, $callable);

        $this->assertSame($result, $req->cancel($expected));
    }

    /**
     * @param string $pid
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return Request
     */
    public function createRequest($pid, callable $success = null, callable $failure = null, callable $abort = null, $timeout = 0.0)
    {
        return new Request($pid, $success, $failure, $abort, $timeout);
    }
}
