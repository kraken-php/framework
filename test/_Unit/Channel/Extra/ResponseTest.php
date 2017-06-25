<?php

namespace Kraken\_Unit\Channel\Extra;

use Kraken\Channel\Extra\Response;
use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\Protocol\ProtocolInterface;
use Kraken\Channel\Channel;
use Dazzle\Loop\Loop;
use Dazzle\Promise\Promise;
use Dazzle\Promise\PromiseInterface;
use Kraken\Test\TUnit;
use Error;
use Exception;

class ResponseTest extends TUnit
{
    /**
     *
     */
    public function testApiConstrutor_DoesNotThrowException()
    {
        $this->createResponse($this->createProtocol(), 'secret');
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $response = $this->createResponse($this->createProtocol(), 'secret');
        unset($response);
    }

    /**
     *
     */
    public function testApiInvoke_CallsSendMethodWithPromise()
    {
        $rep = $this->createResponse($this->createProtocol(), 'secret', [], [ 'send' ]);
        $rep
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function($promise) {
                $this->assertInstanceOf(PromiseInterface::class, $promise);
                return $promise;
            }));

        $rep();
    }

    /**
     *
     */
    public function testApiCall_CallsSendMethodWithPromise()
    {
        $rep = $this->createResponse($this->createProtocol(), 'secret', [], [ 'send' ]);
        $rep
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function($promise) {
                $this->assertInstanceOf(PromiseInterface::class, $promise);
                return $promise;
            }));

        $rep->call();
    }

    /**
     *
     */
    public function testApiSend_SendsMessageAsMessage_OnSuccess()
    {
        $protocol = $this->createProtocol();
        $rep = $this->createResponse($protocol, 'secret');
        $promise = new Promise();

        $channel = $this->getProtectedProperty($rep, 'channel');
        $channel
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function($origin, ProtocolInterface $answer, $mode) {
                $this->assertSame('secret', $answer->getMessage());
                $this->assertSame('', $answer->getException());
                $this->assertSame(Channel::MODE_BUFFER_ONLINE, $mode);
            }));

        $this->callProtectedMethod($rep, 'send', [ $promise ]);
    }

    /**
     *
     */
    public function testApiSend_SendsMessageAsException_OnFailure()
    {
        $protocol = $this->createProtocol();
        $rep = $this->createResponse($protocol, $ex = new Exception('secret'));
        $promise = new Promise();

        $channel = $this->getProtectedProperty($rep, 'channel');
        $channel
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function($origin, ProtocolInterface $answer, $mode) use($ex) {
                $this->assertSame('secret', $answer->getMessage());
                $this->assertSame(get_class($ex), $answer->getException());
                $this->assertSame(Channel::MODE_BUFFER_ONLINE, $mode);
            }));

        $this->callProtectedMethod($rep, 'send', [ $promise ]);
    }

    /**
     * @param ProtocolInterface $protocol
     * @param string|string[]|Error|Exception $message
     * @param mixed[] $params
     * @param string[] $methods
     * @return Response|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createResponse($protocol, $message, $params = [], $methods = [])
    {
        return $this->getMock(
            Response::class,
            $methods,
            [ $this->createChannelMock('channelName'), $protocol, $message, $params ]
        );
    }

    /**
     * @return Protocol
     */
    public function createProtocol()
    {
        return new Protocol('', 'uniqueID', '', '', '', '', 0);
    }

    /**
     * @param string $name
     * @return Channel|\PHPUnit_Framework_MockObject_MockObject
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
            ->method('onTick')
            ->with($this->isType('callable'))
            ->will($this->returnCallback(function($callable) {
                $callable();
            }));

        $channel = $this->getMock(Channel::class, [], [], '', false);
        $channel
            ->expects($this->atMost(1))
            ->method('createProtocol')
            ->will($this->returnCallback(function($message = null) use($name) {
                return new Protocol('', 'uniqueID', '', $name, $message, '', 0);
            }));
        $channel
            ->expects($this->any())
            ->method('getLoop')
            ->will($this->returnValue($loop));

        return $channel;
    }
}
