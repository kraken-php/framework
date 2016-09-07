<?php

namespace Kraken\_Unit\Channel\Router;

use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\Router\Router;
use Kraken\Channel\Router\RouterInterface;
use Kraken\Channel\Router\RouterRule;
use Kraken\Test\TUnit;

class RouterRuleTest extends TUnit
{
    /**
     * @var RouterInterface
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
     * @return Protocol
     */
    public function createProtocol()
    {
        return new Protocol();
    }

    /**
     * @param callable $matcher
     * @param callable $handler
     * @param bool $propagate
     * @param int $limit
     * @return RouterRule|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRouterHandler(callable $matcher, callable $handler, $propagate = false, $limit = 0)
    {
        $this->router = new Router();

        return $this->getMock(RouterRule::class, [ 'cancel' ], [ $this->router, $matcher, $handler, $propagate, $limit ]);
    }
}
