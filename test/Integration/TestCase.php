<?php

namespace Kraken\Test\Integration;

use Kraken\Event\BaseEventEmitter;
use Kraken\Event\EventEmitterInterface;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopExtendedInterface;
use Kraken\Loop\Model\StreamSelectLoop;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\Unit\Stub\Event;
use Kraken\Test\Unit\Stub\EventCollection;

class TestCase extends \Kraken\Test\Unit\TestCase
{
    /**
     * @var string
     */
    const MSG_EVENT_NAME_ASSERTION_FAILED = 'Expected event name mismatch.';

    /**
     * @var string
     */
    const MSG_EVENT_DATA_ASSERTION_FAILED = 'Expected event data mismatch.';

    /**
     * @var string
     */
    const MSG_EVENT_GET_ASSERTION_FAILED = "Expected event mismatch.\nExpected event %s, got event %s.";

    /**
     * @var LoopExtendedInterface
     */
    protected static $loop;

    /**
     * @return LoopExtendedInterface
     */
    public function loop()
    {
        return self::$loop;
    }

    /**
     * Prepare simulation environment
     */
    public static function setUpBeforeClass()
    {
        self::$loop = new Loop(new StreamSelectLoop());
    }

    /**
     *
     */
    public function setUp()
    {
        self::$loop->flush(true);
    }

    /**
     * Run test scenario as simulation.
     *
     * @param callable(TestCase) $simulation
     * @return PromiseInterface
     */
    public function simulate(callable $simulation)
    {
        $data = $simulation($this);
        $promise = new Promise();

        $loop = $this->loop();
        $loop->addTimer(15, function() use($loop) {
            $loop->stop();
            $this->fail('Timeout for test has been reached.');
        });
        $loop->start();

        return $promise->resolve($data);
    }

    /**
     * @return EventCollection
     */
    public function createEventCollection()
    {
        return new EventCollection();
    }

    /**
     * @param string $name
     * @param mixed[] $data
     * @return Event
     */
    public function createEvent($name, $data = [])
    {
        return new Event($name, $data);
    }

    /**
     * @param Event[] $actualEvents
     * @param Event[] $expectedEvents
     */
    public function assertEvents($actualEvents = [], $expectedEvents = [])
    {
        $count = max(count($actualEvents), count($expectedEvents));

        for ($i=0; $i<$count; ++$i)
        {
            if (!isset($actualEvents[$i]))
            {
                $this->fail(
                    sprintf(self::MSG_EVENT_GET_ASSERTION_FAILED, $expectedEvents[$i]->name(), 'null')
                );
            }
            else if (!isset($expectedEvents[$i]))
            {
                $this->fail(
                    sprintf(self::MSG_EVENT_GET_ASSERTION_FAILED, 'null', $actualEvents[$i]->name())
                );
            }

            $actualEvent = $actualEvents[$i];
            $expectedEvent = $expectedEvents[$i];

            $this->assertEquals($expectedEvent->name(), $actualEvent->name(), self::MSG_EVENT_NAME_ASSERTION_FAILED);
            $this->assertEquals($expectedEvent->data(), $actualEvent->data(), self::MSG_EVENT_DATA_ASSERTION_FAILED);
        }
    }
}
