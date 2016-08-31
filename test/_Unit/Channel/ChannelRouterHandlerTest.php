<?php

namespace Kraken\_Unit\Channel;

use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\ChannelRouter;
use Kraken\Channel\ChannelRouterInterface;
use Kraken\Channel\ChannelRouterHandler;
use Kraken\Test\TUnit;

class ChannelRouterHandlerTest extends TUnit
{
    /**
     * @var ChannelRouterInterface
     */
    private $router;

    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createRouterHandler(function() {}, function() {});
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $handler = $this->createRouterHandler(function() {}, function() {});
        unset($handler);
    }

    /**
     *
     */
    public function testApiRouter_ReturnsRouter()
    {
        $handler = $this->createRouterHandler(function() {}, function() {});

        $this->assertSame($this->router, $handler->getRouter());
    }

    /**
     *
     */
    public function testApiMatch_CallsMatcher()
    {
        $name = 'name';
        $protocol = $this->createProtocol();

        $matcher = $this->createCallableMock();
        $matcher
            ->expects($this->once())
            ->method('__invoke')
            ->with($name, $protocol);

        $handler = $this->createRouterHandler($matcher, function() {});
        $handler->match($name, $protocol);
    }

    /**
     *
     */
    public function testApiHandle_CallsHandler()
    {
        $name = 'name';
        $protocol = $this->createProtocol();
        $flags = 1;
        $success = function() {};
        $failure = function() {};
        $abort   = function() {};
        $timeout = 2.0;

        $handler = $this->createCallableMock();
        $handler
            ->expects($this->once())
            ->method('__invoke')
            ->with($name, $protocol, $flags, $success, $failure, $abort, $timeout);

        $propagate = false;
        $handler = $this->createRouterHandler(function() {}, $handler, $propagate);
        $result = $handler->handle($name, $protocol, $flags, $success, $failure, $abort, $timeout);

        $this->assertSame($propagate, $result);
    }

    /**
     *
     */
    public function testApiHandle_DecreasesLimit()
    {
        $name = 'name';
        $protocol = $this->createProtocol();
        $flags = 1;
        $limit = 3;

        $handler = $this->createRouterHandler(function() {}, function() {}, false, $limit);
        $prop = $this->getProtectedProperty($handler, 'limit');
        $this->assertSame($limit, $prop);

        $handler->handle($name, $protocol, $flags);
        $prop = $this->getProtectedProperty($handler, 'limit');
        $this->assertSame($limit-1, $prop);
    }

    /**
     *
     */
    public function testApiHandle_CallsCancelWhenLimitHitsZero()
    {
        $name = 'name';
        $protocol = $this->createProtocol();
        $flags = 1;
        $limit = 1;

        $handler = $this->createRouterHandler(function() {}, function() {}, false, $limit);
        $handler
            ->expects($this->once())
            ->method('cancel');

        $handler->handle($name, $protocol, $flags);
    }

    /**
     *
     */
    public function testApiSetPointer_SetsPointer()
    {
        $handler = $this->createRouterHandler(function() {}, function() {});

        $result = $this->getProtectedProperty($handler, 'pointer');
        $this->assertSame(null, $result);

        $handler->setPointer('test', 5);

        $result = $this->getProtectedProperty($handler, 'pointer');
        $this->assertSame([ 'test', 5 ], $result);
    }

    /**
     * @return ChannelProtocol
     */
    public function createProtocol()
    {
        return new ChannelProtocol();
    }

    /**
     * @param callable $matcher
     * @param callable $handler
     * @param bool $propagate
     * @param int $limit
     * @return ChannelRouterHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRouterHandler(callable $matcher, callable $handler, $propagate = false, $limit = 0)
    {
        $this->router = new ChannelRouter();

        return $this->getMock(ChannelRouterHandler::class, [ 'cancel' ], [ $this->router, $matcher, $handler, $propagate, $limit ]);
    }
}
