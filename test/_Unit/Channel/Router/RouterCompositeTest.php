<?php

namespace Kraken\_Unit\Channel\Router;

use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\Router\Router;
use Kraken\Channel\Router\RouterInterface;
use Kraken\Channel\Router\RouterComposite;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;
use Kraken\Test\TUnit;

class RouterCompositeTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createChannelRouterComposite();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $router = $this->createChannelRouterComposite();
        unset($router);
    }

    /**
     *
     */
    public function testApiGetBus_ReturnsBus_WhenBusDoesExist()
    {
        $base   = $this->createChannelRouter();
        $router = $this->createChannelRouterComposite([ 'bus' => $base ]);

        $this->assertSame($base, $router->getBus('bus'));
    }

    /**
     *
     */
    public function testApiGetBus_ThrowsException_WhenBusDoesNotExist()
    {
        $router = $this->createChannelRouterComposite();

        $this->setExpectedException(ResourceUndefinedException::class);
        $router->getBus('bus');
    }

    /**
     *
     */
    public function testApiSetBus_SetsBus()
    {
        $base   = $this->createChannelRouter();
        $router = $this->createChannelRouterComposite();

        $this->assertFalse($router->existsBus('bus'));
        $router->setBus('bus', $base);
        $this->assertTrue($router->existsBus('bus'));
    }

    /**
     *
     */
    public function testApiExistsBus_ReturnsTrue_WhenBusDoesExist()
    {
        $base   = $this->createChannelRouter();
        $router = $this->createChannelRouterComposite([ 'bus' => $base ]);

        $this->assertTrue($router->existsBus('bus'));
    }

    /**
     *
     */
    public function testApiExistsBus_ReturnsFalse_WhenBusDoesNotExist()
    {
        $router = $this->createChannelRouterComposite();

        $this->assertFalse($router->existsBus('bus'));
    }

    /**
     *
     */
    public function testApiRemoveBus_RemovesBus_WhenBusDoesExist()
    {
        $base   = $this->createChannelRouter();
        $router = $this->createChannelRouterComposite([ 'bus' => $base ]);

        $this->assertTrue($router->existsBus('bus'));
        $router->removeBus('bus');
        $this->assertFalse($router->existsBus('bus'));
    }

    /**
     *
     */
    public function testApiRemoveBus_DoesNothing_WhenBusDoesNotExist()
    {
        $router = $this->createChannelRouterComposite();

        $this->assertFalse($router->existsBus('bus'));
        $router->removeBus('bus');
        $this->assertFalse($router->existsBus('bus'));
    }

    /**
     *
     */
    public function testApiGetBuses_ReturnsAllBuses()
    {
        $bus1 = $this->createChannelRouter();
        $bus2 = $this->createChannelRouter();
        $buses = [ 'bus1' => $bus1, 'bus2' => $bus2 ];
        $router = $this->createChannelRouterComposite($buses);

        $this->assertSame($buses, $router->getBuses());
    }

    /**
     *
     */
    public function testApiHandle_CallsHandleOnAllBuses()
    {
        $name = 'name';
        $protocol = $this->createProtocol();
        $flags = 1;
        $success = function() {};
        $failure = function() {};
        $abort   = function() {};
        $timeout = 2.0;

        $bus1 = $this->createChannelRouter();
        $bus1
            ->expects($this->once())
            ->method('handle')
            ->with('bus1', $protocol, $flags, $success, $failure, $abort, $timeout);

        $bus2 = $this->createChannelRouter();
        $bus2
            ->expects($this->once())
            ->method('handle')
            ->with('bus2', $protocol, $flags, $success, $failure, $abort, $timeout);

        $router = $this->createChannelRouterComposite([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $router->handle($name, $protocol, $flags, $success, $failure, $abort, $timeout);
    }

    /**
     *
     */
    public function testApiErase_CallsEraseOnAllBuses()
    {
        $bus1 = $this->createChannelRouter();
        $bus1
            ->expects($this->once())
            ->method('erase');

        $bus2 = $this->createChannelRouter();
        $bus2
            ->expects($this->once())
            ->method('erase');

        $router = $this->createChannelRouterComposite([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $router->erase();
    }

    /**
     *
     */
    public function testApiAddRule_CallsAddRuleOnAllBuses()
    {
        $matcher = function() {};
        $handler = function() {};
        $propagate = true;
        $limit = 2;

        $bus1 = $this->createChannelRouter();
        $bus1
            ->expects($this->once())
            ->method('addRule')
            ->with($matcher, $handler, $propagate, $limit);

        $bus2 = $this->createChannelRouter();
        $bus2
            ->expects($this->once())
            ->method('addRule')
            ->with($matcher, $handler, $propagate, $limit);

        $router = $this->createChannelRouterComposite([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $router->addRule($matcher, $handler, $propagate, $limit);
    }

    /**
     *
     */
    public function testApiAddAnchor_CallsAddAnchorOnAllBuses()
    {
        $handler = function() {};
        $propagate = true;
        $limit = 2;

        $bus1 = $this->createChannelRouter();
        $bus1
            ->expects($this->once())
            ->method('addAnchor')
            ->with($handler, $propagate, $limit);

        $bus2 = $this->createChannelRouter();
        $bus2
            ->expects($this->once())
            ->method('addAnchor')
            ->with($handler, $propagate, $limit);

        $router = $this->createChannelRouterComposite([ 'bus1' => $bus1, 'bus2' => $bus2 ]);

        $router->addAnchor($handler, $propagate, $limit);
    }


    /**
     * @return Protocol
     */
    public function createProtocol()
    {
        return new Protocol();
    }

    /**
     * @return RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createChannelRouter()
    {
        return $this->getMock(Router::class, [], [], '', false);
    }

    /**
     * @param RouterInterface[] $buses
     * @return RouterComposite
     */
    public function createChannelRouterComposite($buses = [])
    {
        return new RouterComposite($buses);
    }
}
