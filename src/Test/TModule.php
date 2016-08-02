<?php

namespace Kraken\Test;

use Kraken\Loop\Model\SelectLoop;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopExtendedInterface;
use Kraken\Loop\LoopInterface;
use Kraken\Test\Stub\Event;
use Kraken\Test\Stub\Simulation;
use Exception;

class TModule extends TUnit
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
    const MSG_EVENT_GET_ASSERTION_FAILED = "Expected event count mismatch after %s events.\nExpected event %s, got event %s.";

    /**
     * @var LoopExtendedInterface
     */
    private $loop;

    /**
     * @var Simulation
     */
    private $simulation;

    /**
     *
     */
    public function setUp()
    {
        $this->loop = new Loop(new SelectLoop);
        $this->loop->flush(true);

        $this->simulation = new Simulation($this->loop);
    }

    /**
     *
     */
    public function tearDown()
    {
        unset($this->simulation);
        unset($this->loop);
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * Run test scenario as simulation.
     *
     * @param callable(Simulation) $scenario
     * @return TModule
     */
    public function simulate(callable $scenario)
    {
        try
        {
            $this->simulation->setScenario($scenario);
            $this->simulation->begin();
        }
        catch (Exception $ex)
        {
            $this->fail($ex->getMessage());
        }

        return $this;
    }

    /**
     * @param $events
     * @return TModule
     */
    public function expect($events)
    {
        $expectedEvents = [];

        foreach ($events as $event)
        {
            $data = isset($event[1]) ? $event[1] : [];
            $expectedEvents[] = new Event($event[0], $data);
        }

        $this->assertEvents(
            $this->simulation->getExpectedEvents(),
            $expectedEvents
        );

        return $this;
    }

    /**
     * @param Event[] $actualEvents
     * @param Event[] $expectedEvents
     */
    public function assertEvents($actualEvents = [], $expectedEvents = [])
    {
        $count = max(count($actualEvents), count($expectedEvents));

        for ($i=0; $i<$count; $i++)
        {
            if (!isset($actualEvents[$i]))
            {
                $this->fail(
                    sprintf(self::MSG_EVENT_GET_ASSERTION_FAILED, $i, $expectedEvents[$i]->name(), 'null')
                );
            }
            else if (!isset($expectedEvents[$i]))
            {
                $this->fail(
                    sprintf(self::MSG_EVENT_GET_ASSERTION_FAILED, $i, 'null', $actualEvents[$i]->name())
                );
            }

            $actualEvent = $actualEvents[$i];
            $expectedEvent = $expectedEvents[$i];

            $this->assertEquals($expectedEvent->name(), $actualEvent->name(), self::MSG_EVENT_NAME_ASSERTION_FAILED);
            $this->assertEquals($expectedEvent->data(), $actualEvent->data(), self::MSG_EVENT_DATA_ASSERTION_FAILED);
        }
    }
}
