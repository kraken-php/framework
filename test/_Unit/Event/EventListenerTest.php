<?php

namespace Kraken\_Unit\Event;

use Kraken\Event\EventEmitterInterface;
use Kraken\Event\EventListener;
use Kraken\Test\TUnit;

class EventListenerTest extends TUnit
{
    /**
     * @dataProvider handlerProvider
     */
    public function testApiGetEmitter_ReturnsEmitter(EventListener $handler, $params)
    {
        $this->assertSame($params['emitter'], $handler->getEmitter());
    }

    /**
     * @dataProvider handlerProvider
     */
    public function testApiGetEvent_ReturnsEvent(EventListener $handler, $params)
    {
        $this->assertEquals($params['event'], $handler->getEvent());
    }

    /**
     * @dataProvider handlerProvider
     */
    public function testApiGetHandler_ReturnsHandler(EventListener $handler, $params)
    {
        $this->assertSame($params['handler'], $handler->getHandler());
    }

    /**
     * @dataProvider handlerProvider
     */
    public function testApiGetListener_ReturnsListener_WhenListenerIsPresent(EventListener $handler, $params)
    {
        $this->assertSame($params['listener'], $handler->getListener());
    }

    /**
     *
     */
    public function testApiGetListener_ReturnsHandler_WhenListenerIsAbsent()
    {
        $handler = new EventListener(
            $gemitter   = $this->getEmitter(),
            $gevent     = $this->getEvent(),
            $ghandler   = $this->getHandler()
        );

        $this->assertSame($ghandler, $handler->getListener());
    }

    /**
     * @dataProvider handlerProvider
     */
    public function testApiCancel_CallsRemoveListenerOnEmitter(EventListener $handler, $params)
    {
        $emitter = $params['emitter'];
        $emitter
            ->expects($this->once())
            ->method('removeListener')
            ->will($this->returnCallback(function($event, $handler) use($params) {
                $this->assertEquals($params['event'], $event);
                $this->assertSame($params['handler'], $handler);
            }));

        $handler->cancel();
    }

    /**
     * @return mixed[][]
     */
    public function handlerProvider()
    {
        return [
            [
                new EventListener(
                    $emitter    = $this->getEmitter(),
                    $event      = $this->getEvent(),
                    $handler    = $this->getHandler(),
                    $listener   = $this->getListener()
                ),
                [
                    'emitter'   => $emitter,
                    'event'     => $event,
                    'handler'   => $handler,
                    'listener'  => $listener
                ]
            ]
        ];
    }

    /**
     * @return EventEmitterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEmitter()
    {
        return $this->getMock('Kraken\Event\EventEmitter');
    }

    /**
     * @return string
     */
    protected function getEvent()
    {
        return 'test';
    }

    /**
     * @return callable
     */
    protected function getHandler()
    {
        return function() {};
    }

    /**
     * @return callable
     */
    protected function getListener()
    {
        return function() {};
    }
}
