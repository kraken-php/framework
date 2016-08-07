<?php

namespace Kraken\_Unit\Channel;

use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\ChannelRouter;
use Kraken\Channel\ChannelRouterBase;
use Kraken\Channel\ChannelRouterHandler;
use Kraken\Test\TUnit;

class ChannelRouterBaseTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createChannelRouter();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $router = $this->createChannelRouter();
        unset($router);
    }

    /**
     *
     */
    public function testApiHandle_HandlesFirstMatchedRule_WhenPropagateSetToFalse()
    {
        $router = $this->createChannelRouter();
        $name = 'name';
        $protocol = $this->createProtocol();

        $matcher = function() { return true; };
        $passer  = function() { return false; };

        $router->addRule($passer,  $this->expectCallableNever());
        $router->addRule($passer,  $this->expectCallableNever());
        $router->addRule($matcher, $this->expectCallableOnce());

        $router->handle($name, $protocol);
    }

    /**
     *
     */
    public function testApiHandle_HandlesAllRules_UpToRuleWithPropagateSetToFalse()
    {
        $router = $this->createChannelRouter();
        $name = 'name';
        $protocol = $this->createProtocol();

        $matcher = function() { return true; };
        $passer  = function() { return false; };

        $router->addRule($matcher, $this->expectCallableOnce(), true);
        $router->addRule($matcher, $this->expectCallableOnce());
        $router->addRule($passer,  $this->expectCallableNever());

        $router->handle($name, $protocol);
    }

    /**
     *
     */
    public function testApiHandle_HandlesFirstMatchedDefault_WhenNoneRulesMatchesAndPropagateSetToFalse()
    {
        $router = $this->createChannelRouter();
        $name = 'name';
        $protocol = $this->createProtocol();

        $matcher = function() { return true; };
        $passer  = function() { return false; };

        $router->addRule($passer, $this->expectCallableNever());
        $router->addAnchor($this->expectCallableOnce());
        $router->addAnchor($this->expectCallableNever());

        $router->handle($name, $protocol);
    }

    /**
     *
     */
    public function testApiHandle_HandlesAllDefaults_WhenNoneRulesMatchesUpToDefaultWithPropagateSetToFalse()
    {
        $router = $this->createChannelRouter();
        $name = 'name';
        $protocol = $this->createProtocol();

        $matcher = function() { return true; };
        $passer  = function() { return false; };

        $router->addRule($passer, $this->expectCallableNever());
        $router->addAnchor($this->expectCallableOnce(), true);
        $router->addAnchor($this->expectCallableOnce());

        $router->handle($name, $protocol);
    }

    /**
     *
     */
    public function testApiHandle_ReturnsTrue_WhenAtLeastOneRuleMatched_ForModeSetToRouter()
    {
        $router = $this->createChannelRouter(ChannelRouter::MODE_ROUTER);
        $name = 'name';
        $protocol = $this->createProtocol();

        $matcher  = function() { return true; };
        $passer   = function() { return false; };
        $callable = function() {};

        $router->addRule($matcher, $callable);

        $status = $router->handle($name, $protocol);

        $this->assertTrue($status);
    }

    /**
     *
     */
    public function testApiHandle_ReturnsTrue_WhenAtLeastOneDefaultMatched_ForModeSetToRouter()
    {
        $router = $this->createChannelRouter(ChannelRouter::MODE_ROUTER);
        $name = 'name';
        $protocol = $this->createProtocol();
        $callable = function() {};

        $router->addAnchor($callable);

        $status = $router->handle($name, $protocol);

        $this->assertTrue($status);
    }

    /**
     *
     */
    public function testApiHandle_ReturnsFalse_WhenNothingMatched_ForModeSetToRouter()
    {
        $router = $this->createChannelRouter(ChannelRouter::MODE_ROUTER);
        $name = 'name';
        $protocol = $this->createProtocol();

        $status = $router->handle($name, $protocol);

        $this->assertFalse($status);
    }

    /**
     *
     */
    public function testApiHandle_ReturnsFalse_WhenAtLeastOneRuleMatched_ForModeSetToFirewall()
    {
        $router = $this->createChannelRouter(ChannelRouter::MODE_FIREWALL);
        $name = 'name';
        $protocol = $this->createProtocol();

        $matcher  = function() { return true; };
        $passer   = function() { return false; };
        $callable = function() {};

        $router->addRule($matcher, $callable);

        $status = $router->handle($name, $protocol);

        $this->assertFalse($status);
    }

    /**
     *
     */
    public function testApiHandle_ReturnsFalse_WhenAtLeastOneDefaultMatched_ForModeSetToFirewall()
    {
        $router = $this->createChannelRouter(ChannelRouter::MODE_FIREWALL);
        $name = 'name';
        $protocol = $this->createProtocol();
        $callable = function() {};

        $router->addAnchor($callable);

        $status = $router->handle($name, $protocol);

        $this->assertFalse($status);
    }

    /**
     *
     */
    public function testApiHandle_ReturnsTrue_WhenNothingMatched_ForModeSetToFirewall()
    {
        $router = $this->createChannelRouter(ChannelRouter::MODE_FIREWALL);
        $name = 'name';
        $protocol = $this->createProtocol();

        $status = $router->handle($name, $protocol);

        $this->assertTrue($status);
    }

    /**
     *
     */
    public function testApiHandle_PassesArgumentsToRuleHandler()
    {
        $router = $this->createChannelRouter();
        $matcher = function() { return true; };

        $name = 'name';
        $protocol = $this->createProtocol();
        $flags = 1;
        $success = function() {};
        $failure = function() {};
        $abort   = function() {};
        $timeout = 2.0;

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($name, $protocol, $flags, $success, $failure, $abort, $timeout);

        $router->addRule($matcher, $callable);
        $router->handle($name, $protocol, $flags, $success, $failure, $abort, $timeout);
    }

    /**
     *
     */
    public function testApiErase_ErasesRulesAndDefaults()
    {
        $router = $this->createChannelRouter();

        $matcher = function() {};
        $handler = function() {};

        $rule = $this->getMock(ChannelRouterHandler::class, [], [ $router, $matcher, $handler ]);
        $rule
            ->expects($this->once())
            ->method('cancel');

        $default = $this->getMock(ChannelRouterHandler::class, [], [ $router, function() {}, $handler ]);
        $default
            ->expects($this->once())
            ->method('cancel');

        $this->callProtectedMethod($router, 'addRuleHandler',    [ $rule ]);
        $this->callProtectedMethod($router, 'addDefaultHandler', [ $default ]);

        $rules = $this->getProtectedProperty($router, 'rules');
        $rulesPointer = $this->getProtectedProperty($router, 'rulesPointer');
        $this->assertCount(1, $rules);
        $this->assertSame (1, $rulesPointer);

        $rules = $this->getProtectedProperty($router, 'anchors');
        $rulesPointer = $this->getProtectedProperty($router, 'anchorsPointer');
        $this->assertCount(1, $rules);
        $this->assertSame (1, $rulesPointer);

        $router->erase();

        $rules = $this->getProtectedProperty($router, 'rules');
        $rulesPointer = $this->getProtectedProperty($router, 'rulesPointer');
        $this->assertCount(0, $rules);
        $this->assertSame (0, $rulesPointer);

        $rules = $this->getProtectedProperty($router, 'anchors');
        $rulesPointer = $this->getProtectedProperty($router, 'anchorsPointer');
        $this->assertCount(0, $rules);
        $this->assertSame (0, $rulesPointer);
    }

    /**
     *
     */
    public function testApiAddRule_AddsRule()
    {
        $router = $this->createChannelRouter();

        $matcher = function() {};
        $handler = function() {};

        $rules = $this->getProtectedProperty($router, 'rules');
        $rulesPointer = $this->getProtectedProperty($router, 'rulesPointer');
        $this->assertCount(0, $rules);
        $this->assertSame (0, $rulesPointer);

        $router->addRule($matcher, $handler);

        $rules = $this->getProtectedProperty($router, 'rules');
        $rulesPointer = $this->getProtectedProperty($router, 'rulesPointer');
        $this->assertCount(1, $rules);
        $this->assertSame (1, $rulesPointer);
    }

    /**
     *
     */
    public function testApiAddAnchor_AddsDefault()
    {
        $router = $this->createChannelRouter();

        $handler = function() {};

        $rules = $this->getProtectedProperty($router, 'anchors');
        $rulesPointer = $this->getProtectedProperty($router, 'anchorsPointer');
        $this->assertCount(0, $rules);
        $this->assertSame (0, $rulesPointer);

        $router->addAnchor($handler);

        $rules = $this->getProtectedProperty($router, 'anchors');
        $rulesPointer = $this->getProtectedProperty($router, 'anchorsPointer');
        $this->assertCount(1, $rules);
        $this->assertSame (1, $rulesPointer);
    }

    /**
     *
     */
    public function testApiRemoveHandler_RemovesRuleHandler()
    {
        $router = $this->createChannelRouter();

        $matcher = function() {};
        $handler = function() {};

        $router->addRule($matcher, $handler);

        $rules = $this->getProtectedProperty($router, 'rules');
        $this->assertCount(1, $rules);

        $router->removeHandler('rules', 0);

        $rules = $this->getProtectedProperty($router, 'rules');
        $this->assertCount(0, $rules);
    }

    /**
     *
     */
    public function testApiRemoveHandler_RemovesDefaultHandler()
    {
        $router = $this->createChannelRouter();

        $handler = function() {};

        $router->addAnchor($handler);

        $rules = $this->getProtectedProperty($router, 'anchors');
        $this->assertCount(1, $rules);

        $router->removeHandler('anchors', 0);

        $rules = $this->getProtectedProperty($router, 'rules');
        $this->assertCount(0, $rules);
    }

    /**
     *
     */
    public function testApiAddRuleHandler_AddsRuleHandler()
    {
        $router = $this->createChannelRouter();

        $matcher = function() {};
        $handler = function() {};

        $rule = new ChannelRouterHandler($router, $matcher, $handler);

        $this->callProtectedMethod($router, 'addRuleHandler', [ $rule ]);

        $rules = $this->getProtectedProperty($router, 'rules');
        $rulesPointer = $this->getProtectedProperty($router, 'rulesPointer');
        $this->assertCount(1, $rules);
        $this->assertSame (1, $rulesPointer);
    }

    /**
     *
     */
    public function testApiAddDefaultHandler_AddsDefaultHandler()
    {
        $router = $this->createChannelRouter();

        $matcher = function() {};
        $handler = function() {};

        $rule = new ChannelRouterHandler($router, $matcher, $handler);

        $this->callProtectedMethod($router, 'addRuleHandler', [ $rule ]);

        $rules = $this->getProtectedProperty($router, 'rules');
        $rulesPointer = $this->getProtectedProperty($router, 'rulesPointer');
        $this->assertCount(1, $rules);
        $this->assertSame (1, $rulesPointer);
    }

    /**
     * @return ChannelProtocol
     */
    public function createProtocol()
    {
        return new ChannelProtocol();
    }

    /**
     * @param int $flags
     * @return ChannelRouterBase
     */
    public function createChannelRouter($flags = ChannelRouter::MODE_DEFAULT)
    {
        return new ChannelRouterBase($flags);
    }
}
