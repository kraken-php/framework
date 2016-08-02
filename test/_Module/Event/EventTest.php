<?php

namespace Kraken\_Module\Event;

use Kraken\Event\EventEmitter;
use Kraken\Test\TModule;

/**
 * @runTestsInSeparateProcesses
 */
class EventTest extends TModule
{
    /**
     *
     */
    public function testEventEmitter_SupportsBigAmountOfEventHandlers_ForTheSameEvent()
    {
        $emitter = new EventEmitter();

        $cnt = 0;
        for ($i = 0; $i < 1e4; $i++)
        {
            $emitter->on('event', function() use(&$cnt) {
                $cnt++;
            });
        }

        $emitter->emit('event');

        $this->assertEquals(1e4, $cnt);
    }

    /**
     *
     */
    public function testEventEmitter_SupportsBigAmountOfEventHandlers_ForDifferentEvents()
    {
        $emitter = new EventEmitter();

        $cnt = 0;
        for ($i = 0; $i < 1e4; $i++)
        {
            $emitter->on(sprintf("event[%s]", $i), function() use($i, &$cnt) {
                $cnt = $i;
            });
        }

        $emitter->emit('event[256]');

        $this->assertEquals(256, $cnt);
    }
}
