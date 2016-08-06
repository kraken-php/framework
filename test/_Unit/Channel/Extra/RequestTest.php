<?php

namespace Kraken\_Unit\Channel\Extra;

use Kraken\Channel\Channel;
use Kraken\Channel\Extra\Request;
use Kraken\Channel\ChannelBase;
use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\ChannelProtocolInterface;
use Kraken\Loop\Loop;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseFulfilled;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Runtime\Execution\TimeoutException;
use Kraken\Throwable\Exception\System\TaskIncompleteException;
use Closure;
use Exception;

class RequestTest extends TUnit
{
    /**
     *
     */
    public function testApiConstrutor_DoesNotThrowException()
    {
        $this->createRequest('name', 'secret');
    }

    /**
     *
     */
    public function testApiConstrutor_TurnsStringMessageToProtocol()
    {
        $req = $this->createRequest('name', $secret = 'secret');
        $message = $this->getProtectedProperty($req, 'message');

        $this->assertInstanceOf(ChannelProtocol::class, $message);
        $this->assertSame($secret, $message->getMessage());
    }

    /**
     *
     */
    public function testApiConstrutor_AllowsProtocolMessage()
    {
        $name = 'name';
        $secret = 'secret';

        $protocol = new ChannelProtocol();
        $protocol->setMessage($secret);

        $req = $this->createRequest($name, $protocol);
        $message = $this->getProtectedProperty($req, 'message');

        $this->assertInstanceOf(ChannelProtocol::class, $message);
        $this->assertSame($secret, $message->getMessage());
    }

    /**
     *
     */
    public function testApiConstrutor_SetsDefaultParams()
    {
        $req = $this->createRequest('name', 'secret');
        $expected = [
            'timeout'           => 3.0,
            'retriesLimit'      => 6,
            'retriesInterval'   => 2.0
        ];
        $params = $this->getProtectedProperty($req, 'params');

        $this->assertSame($expected, $params);
    }

    /**
     *
     */
    public function testApiConstrutor_SetsPassedParams()
    {
        $expected = [
            'timeout'           => 5.0,
            'retriesLimit'      => 12,
            'retriesInterval'   => 1.0
        ];

        $req = $this->createRequest('name', 'secret', $expected);

        $params = $this->getProtectedProperty($req, 'params');

        $this->assertSame($expected, $params);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $req = $this->createRequest('name', 'secret');
        unset($req);
    }

    /**
     *
     */
    public function testApiInvoke_CallsSendMethodWithPromise()
    {
        $req = $this->createRequest('name', 'secret', [], [ 'send' ]);

        $req
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function($promise) {
                $this->assertInstanceOf(PromiseInterface::class, $promise);
                return $promise;
            }));

        $req();
    }

    /**
     *
     */
    public function testApiCall_CallsSendMethodWithPromise()
    {
        $req = $this->createRequest('name', 'secret', [], [ 'send' ]);

        $req
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function($promise) {
                $this->assertInstanceOf(PromiseInterface::class, $promise);
                return $promise;
            }));

        $req->call();
    }

    /**
     *
     */
    public function testApiSend_ReturnsImmediatelyWhenNonPendingPromisePassed()
    {
        $req = $this->createRequest('name', 'secret');

        $channel = $this->getProtectedProperty($req, 'channel');
        $channel
            ->expects($this->never())
            ->method('send');

        $promise = new PromiseFulfilled();
        $result = $this->callProtectedMethod($req, 'send', [ $promise ]);

        $this->assertSame($result, $promise);
    }

    /**
     *
     */
    public function testApiSend_CallsSendMethodOnChannel()
    {
        $req = $this->createRequest($sname = 'name', $smssg = 'secret');

        $params  = $this->getProtectedProperty($req, 'params');
        $channel = $this->getProtectedProperty($req, 'channel');
        $channel
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(
                function($name, $message, $mode, $success, $failure, $abort, $timeout) use($sname, $smssg, $params) {
                    $this->assertSame($sname, $name);
                    $this->assertSame($smssg, $message->getMessage());
                    $this->assertSame(Channel::MODE_STANDARD, $mode);
                    $this->assertInstanceOf(Closure::class, $success);
                    $this->assertInstanceOf(Closure::class, $failure);
                    $this->assertInstanceOf(Closure::class, $abort);
                    $this->assertSame($params['timeout'], $timeout);
                }
            ));

        $promise = new Promise();
        $result = $this->callProtectedMethod($req, 'send', [ $promise ]);

        $this->assertSame($result, $promise);
    }

    /**
     *
     */
    public function testApiSend_ResolvesPromiseOnSuccess()
    {
        $req = $this->createRequest($name = 'name', $mssg = 'secret');
        $result = '';

        $channel = $this->getProtectedProperty($req, 'channel');
        $channel
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(
                function($name, $message, $mode, $success, $failure, $abort, $timeout) {
                    $success('success');
                }
            ));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnCallback(function($value) use(&$result) {
                $result = $value;
            }));

        $promise = new Promise();
        $promise->then($callable);

        $this->callProtectedMethod($req, 'send', [ $promise ]);

        $this->assertSame('success', $result);
    }

    /**
     *
     */
    public function testApiSend_RejectsPromiseOnFailure()
    {
        $req = $this->createRequest($name = 'name', $mssg = 'secret');
        $result = '';

        $channel = $this->getProtectedProperty($req, 'channel');
        $channel
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(
                function($name, $message, $mode, $success, $failure, $abort, $timeout) {
                    $failure('error');
                }
            ));

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnCallback(function($value) use(&$result) {
                $result = $value;
            }));

        $promise = new Promise();
        $promise->then(null, $callable);

        $this->callProtectedMethod($req, 'send', [ $promise ]);

        $this->assertSame('error', $result);
    }

    /**
     *
     */
    public function testApiSend_TriesToRetryOnCancel()
    {
        $req = $this->createRequest($name = 'name', $mssg = 'secret', [], [ 'retryOrReset' ]);

        $channel = $this->getProtectedProperty($req, 'channel');
        $channel
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(
                function($name, $message, $mode, $success, $failure, $abort, $timeout) {
                    $abort('exception');
                }
            ));

        $promise = new Promise();
        $req
            ->expects($this->once())
            ->method('retryOrReset')
            ->will($this->returnCallback(function($result, $ex) use($promise) {
                $this->assertSame($promise, $result);
                $this->assertSame('exception', $ex);
            }));

        $this->callProtectedMethod($req, 'send', [ $promise ]);
    }

    /**
     *
     */
    public function testApiRetryOrReset_ResetsWhenExceptionIsTaskIncomplete()
    {
        $req = $this->createRequest($name = 'name', $mssg = 'secret', [], [ 'send' ]);
        $ex = new TaskIncompleteException();
        $promise = new Promise();

        $req
            ->expects($this->once())
            ->method('send')
            ->with($promise);

        $this->callProtectedMethod($req, 'retryOrReset', [ $promise, $ex ]);
    }

    /**
     *
     */
    public function testApiRetryOrReset_RejectsPromiseInRetry_WhenRetriesLimitIsReached()
    {
        $req = $this->createRequest($name = 'name', $mssg = 'secret', [ 'retriesLimit' => 0 ]);
        $ex = new Exception();
        $promise = new Promise();

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(TimeoutException::class));

        $promise->then(null, $callable);

        $this->callProtectedMethod($req, 'retryOrReset', [ $promise, $ex ]);
    }

    /**
     *
     */
    public function testApiRetryOrReset_ResetsInRetryUsingTimer_WhenIntervalIsGreaterThanZero()
    {
        $req = $this->createRequest($name = 'name', $mssg = 'secret', [ 'retriesInterval' => 1 ], [ 'send' ]);
        $ex = new Exception();
        $promise = new Promise();

        $req
            ->expects($this->once())
            ->method('send')
            ->with($promise);

        $oldCounter = $this->getProtectedProperty($req, 'counter');
        $this->callProtectedMethod($req, 'retryOrReset', [ $promise, $ex ]);
        $newCounter = $this->getProtectedProperty($req, 'counter');

        $this->assertSame($oldCounter+1, $newCounter);
    }

    /**
     *
     */
    public function testApiRetryOrReset_ResetsInRetry_WhenIntervalIsEqualToZero()
    {
        $req = $this->createRequest($name = 'name', $mssg = 'secret', [ 'retriesInterval' => 0 ], [ 'send' ]);
        $ex = new Exception();
        $promise = new Promise();

        $req
            ->expects($this->once())
            ->method('send')
            ->with($promise);

        $oldCounter = $this->getProtectedProperty($req, 'counter');
        $this->callProtectedMethod($req, 'retryOrReset', [ $promise, $ex ]);
        $newCounter = $this->getProtectedProperty($req, 'counter');

        $this->assertSame($oldCounter+1, $newCounter);
    }

    /**
     * @param string $name
     * @param string|ChannelProtocolInterface $message
     * @param mixed[] $params
     * @param string[] $methods
     * @return Request|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRequest($name, $message, $params = [], $methods = [])
    {
        return $this->getMock(
            Request::class,
            $methods,
            [ $this->createChannelMock('channelName'), $name, $message, $params ]
        );
    }

    /**
     * @param string $name
     * @return ChannelBase|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createChannelMock($name)
    {
        $loop = $this->getMock(Loop::class, [], [], '', false);
        $loop
            ->expects($this->any())
            ->method('addTimer')
            ->with($this->isType('numeric'), $this->isType('callable'))
            ->will($this->returnCallback(function($interval, $callable) {
                $callable();
            }));
        $loop
            ->expects($this->any())
            ->method('afterTick')
            ->with($this->isType('callable'))
            ->will($this->returnCallback(function($callable) {
                $callable();
            }));

        $channel = $this->getMock(ChannelBase::class, [], [], '', false);
        $channel
            ->expects($this->atMost(1))
            ->method('createProtocol')
            ->will($this->returnCallback(function($message = null) use($name) {
                return new ChannelProtocol('', 'uniqueID', '', $name, $message, '', 0);
            }));
        $channel
            ->expects($this->any())
            ->method('getLoop')
            ->will($this->returnValue($loop));

        return $channel;
    }
}
