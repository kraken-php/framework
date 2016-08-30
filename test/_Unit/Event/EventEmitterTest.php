<?php

namespace Kraken\_Unit\Event;

use Kraken\Event\EventEmitter;
use Kraken\Event\EventEmitterInterface;
use Kraken\Loop\LoopInterface;
use Kraken\Test\TUnit;

class EventEmitterTest extends TUnit
{
    /**
     * @dataProvider emitterProvider
     */
    public function testApiSetModeAndGetMode_SetsProperBehaviour(EventEmitterInterface $emitter)
    {
        $this->assertEquals(EventEmitter::EVENTS_DEFAULT, $emitter->getMode());

        $emitter->setMode(EventEmitter::EVENTS_FORWARD);
        $this->assertEquals(EventEmitter::EVENTS_FORWARD, $emitter->getMode());

        $emitter->setMode(EventEmitter::EVENTS_DISCARD);
        $this->assertEquals(EventEmitter::EVENTS_DISCARD, $emitter->getMode());

        $emitter->setMode(EventEmitter::EVENTS_DISCARD_INCOMING);
        $this->assertEquals(EventEmitter::EVENTS_DISCARD_INCOMING, $emitter->getMode());

        $emitter->setMode(EventEmitter::EVENTS_DISCARD_OUTCOMING);
        $this->assertEquals(EventEmitter::EVENTS_DISCARD_OUTCOMING, $emitter->getMode());
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiOn_AttachesProperOnHandler(EventEmitterInterface $emitter)
    {
        $handler = $emitter->on('test', $this->expectCallableTwice());

        $this->assertSame($emitter, $handler->getEmitter());
        $this->assertEquals('test', $handler->getEvent());

        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiOn_AttachesProperOnHandler_UsingSeveralHandlers(EventEmitterInterface $emitter)
    {
        $emitter->on('test', $this->expectCallableTwice());
        $emitter->emit('test');
        $emitter->on('test', $this->expectCallableOnce());
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiOnce_AttachesProperOnceHandler(EventEmitterInterface $emitter)
    {
        $handler = $emitter->once('test', $this->expectCallableOnce());

        $this->assertSame($emitter, $handler->getEmitter());
        $this->assertEquals('test', $handler->getEvent());

        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiOnce_AttachesProperOnceHandler_UsingSeveralHandlers(EventEmitterInterface $emitter)
    {
        $emitter->once('test', $this->expectCallableOnce());
        $emitter->once('test', $this->expectCallableOnce());
        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiTimes_AttachesProperTimesHandler(EventEmitterInterface $emitter)
    {
        $handler = $emitter->times('test', 2, $this->expectCallableExactly(2));

        $this->assertSame($emitter, $handler->getEmitter());
        $this->assertEquals('test', $handler->getEvent());

        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiTimes_AttachesProperTimesHandler_UsingSeveralHandlers(EventEmitterInterface $emitter)
    {
        $emitter->times('A', 2, $this->expectCallableExactly(2));
        $emitter->times('B', 3, $this->expectCallableExactly(3));

        for ($i=0; $i<3; $i++)
        {
            $emitter->emit('A');
            $emitter->emit('B');
        }
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiDelay_AttachesProperDelayHandler(EventEmitterInterface $emitter)
    {
        $handler = $emitter->delay('test', 2, $this->expectCallableOnce());

        $this->assertSame($emitter, $handler->getEmitter());
        $this->assertEquals('test', $handler->getEvent());

        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiDelay_AttachesProperDelayHandler_UsingSeveralHandlers(EventEmitterInterface $emitter)
    {
        $emitter->delay('test', 3, $this->expectCallableExactly(2));
        $emitter->delay('test', 2, $this->expectCallableExactly(3));

        $emitter->emit('test');
        $emitter->emit('test');
        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiDelayOnce_AttachesProperOnceHandler(EventEmitterInterface $emitter)
    {
        $handler = $emitter->delayOnce('test', 2, $this->expectCallableOnce());

        $this->assertSame($emitter, $handler->getEmitter());
        $this->assertEquals('test', $handler->getEvent());

        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiDelayOnce_AttachesProperOnceHandler_UsingSeveralHandlers(EventEmitterInterface $emitter)
    {
        $emitter->delayOnce('test', 2, $this->expectCallableOnce());
        $emitter->delayOnce('test', 3, $this->expectCallableOnce());
        $emitter->emit('test');
        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiDelayTimes_AttachesProperOnceHandler(EventEmitterInterface $emitter)
    {
        $handler = $emitter->delayTimes('test', 2, 2, $this->expectCallableTwice());

        $this->assertSame($emitter, $handler->getEmitter());
        $this->assertEquals('test', $handler->getEvent());

        $emitter->emit('test');
        $emitter->emit('test');
        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiDelayTimes_AttachesProperOnceHandler_UsingSeveralHandlers(EventEmitterInterface $emitter)
    {
        $emitter->delayTimes('test', 2, 2, $this->expectCallableTwice());
        $emitter->delayTimes('test', 3, 1, $this->expectCallableOnce());

        $emitter->emit('test');
        $emitter->emit('test');
        $emitter->emit('test');
        $emitter->emit('test');
        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiRemoveListener_RemovesListener_WhenListenerIsPresent(EventEmitterInterface $emitter)
    {
        $never = $this->expectCallableNever();
        $once = $this->expectCallableOnce();

        $emitter->on('test', $never);
        $emitter->on('test', $once);

        $emitter->removeListener('test', $never);

        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiRemoveListener_DoesNothing_WhenListenerIsAbsent(EventEmitterInterface $emitter)
    {
        $emitter->removeListener('test', function() {});
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiRemoveListener_RemovesListener_ForOnceListeners(EventEmitterInterface $emitter)
    {
        $never = $this->expectCallableNever();
        $once = $this->expectCallableOnce();

        $emitter->once('test', $never);
        $emitter->once('test', $once);

        $emitter->removeListener('test', $never);

        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiRemoveListener_RemovesListener_ForTimesListeners(EventEmitterInterface $emitter)
    {
        $never = $this->expectCallableNever();
        $once = $this->expectCallableOnce();

        $emitter->times('test', 1, $never);
        $emitter->times('test', 1, $once);

        $emitter->removeListener('test', $never);

        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiRemoveListeners_RemovesListeners_WhenListenersArePresent(EventEmitterInterface $emitter)
    {
        $emitter->on('A', $this->expectCallableNever());
        $emitter->on('B', $this->expectCallableNever());
        $emitter->on('C', $this->expectCallableOnce());

        $emitter->removeListeners('A');
        $emitter->removeListeners('B');

        $emitter->emit('A');
        $emitter->emit('B');
        $emitter->emit('C');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiRemoveListeners_DoesNothing_WhenListenersAreAbsent(EventEmitterInterface $emitter)
    {
        $emitter->removeListeners('A');
        $emitter->removeListeners('B');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiFlushListeners_FlushesListeners(EventEmitterInterface $emitter)
    {
        $emitter->on('A', $this->expectCallableNever());
        $emitter->on('B', $this->expectCallableNever());
        $emitter->on('C', $this->expectCallableNever());

        $emitter->flushListeners();

        $emitter->emit('A');
        $emitter->emit('B');
        $emitter->emit('C');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiFindListener_FindsListener(EventEmitterInterface $emitter)
    {
        $fun = function() {};

        $emitter->on('test', $fun);

        $this->assertSame(null, $emitter->findListener('test', function() {}));
        $this->assertSame(0, $emitter->findListener('test', $fun));
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiEmit_EmitsEvents_ForBehaviourSetToForwardEvents(EventEmitterInterface $emitter)
    {
        $listener = $this->createEventEmitter();

        $emitter->setMode(EventEmitter::EVENTS_FORWARD);
        $emitter->forwardEvents($listener);

        $emitter->on('test', $this->expectCallableOnce());
        $listener->on('test', $this->expectCallableOnce());

        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiEmit_EmitsEvents_ForBehaviourSetToDiscardEvents(EventEmitterInterface $emitter)
    {
        $listener = $this->createEventEmitter();

        $emitter->setMode(EventEmitter::EVENTS_DISCARD);
        $emitter->forwardEvents($listener);

        $emitter->on('test', $this->expectCallableNever());
        $listener->on('test', $this->expectCallableNever());

        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiEmit_EmitsEvents_ForBehaviourSetToDiscardIncomingEvents(EventEmitterInterface $emitter)
    {
        $listener = $this->createEventEmitter();

        $emitter->setMode(EventEmitter::EVENTS_DISCARD_INCOMING);
        $emitter->forwardEvents($listener);

        $emitter->on('test', $this->expectCallableNever());
        $listener->on('test', $this->expectCallableOnce());

        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiEmit_EmitsEvents_ForBehaviourSetToDiscardOutcomingEvents(EventEmitterInterface $emitter)
    {
        $listener = $this->createEventEmitter();

        $emitter->setMode(EventEmitter::EVENTS_DISCARD_OUTCOMING);
        $emitter->forwardEvents($listener);

        $emitter->on('test', $this->expectCallableOnce());
        $listener->on('test', $this->expectCallableNever());

        $emitter->emit('test');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiCopyEvent_AttachesOnProxyListenerForEvent(EventEmitterInterface $emitter)
    {
        $listener = $this->createEventEmitter();

        $emitter->copyEvent($listener, 'A');

        $listener->on('A', $this->expectCallableOnce());
        $listener->on('B', $this->expectCallableNever());

        $emitter->emit('A');
        $emitter->emit('B');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiCopyEvents_AttachesOnProxyListenerForEvents(EventEmitterInterface $emitter)
    {
        $listener = $this->createEventEmitter();

        $emitter->copyEvents($listener, [ 'A', 'B' ]);

        $listener->on('A', $this->expectCallableOnce());
        $listener->on('B', $this->expectCallableOnce());

        $emitter->emit('A');
        $emitter->emit('B');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiForwardEvents_AddsEventEmitterForwarder(EventEmitterInterface $emitter)
    {
        $listener = $this->createEventEmitter();

        $emitter->forwardEvents($listener);

        $listener->on('A', $this->expectCallableOnce());
        $listener->on('B', $this->expectCallableOnce());

        $emitter->emit('A');
        $emitter->emit('B');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiDiscardEvents_RemovesEventEmitterForwarder_WhenForwarderIsPresent(EventEmitterInterface $emitter)
    {
        $listener = $this->createEventEmitter();

        $emitter->forwardEvents($listener);

        $listener->on('A', $this->expectCallableOnce());
        $listener->on('B', $this->expectCallableOnce());

        $emitter->emit('A');
        $emitter->emit('B');

        $emitter->discardEvents($listener);

        $emitter->emit('A');
        $emitter->emit('B');
    }

    /**
     * @dataProvider emitterProvider
     */
    public function testApiDiscardEvents_DoesNothing_WhenForwarderIsAbsent(EventEmitterInterface $emitter)
    {
        $listener = $this->createEventEmitter();

        $emitter->discardEvents($listener);

        $emitter->emit('A');
        $emitter->emit('B');
    }

    /**
     * @return EventEmitterInterface[][]
     */
    public function emitterProvider()
    {
        return [
            [ $this->createEventEmitter() ],
            [ $this->createEventEmitter($this->createLoopMock()) ]
        ];
    }

    /**
     * @param LoopInterface|null $loop
     * @return EventEmitterInterface
     */
    protected function createEventEmitter($loop = null)
    {
        return new EventEmitter($loop);
    }

    /**
     * @return LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createLoopMock()
    {
        $loop = parent::createLoopMock();
        $loop
            ->expects($this->any())
            ->method('onTick')
            ->will($this->returnCallback(function($listener, $args = []) {
                call_user_func_array($listener, $args);
            }));

        return $loop;
    }
}
