<?php

namespace Kraken\Test\Integration\Stub;

use Kraken\Loop\Model\StreamSelectLoop;
use Kraken\Loop\Loop;
use Kraken\Loop\LoopExtendedInterface;
use Kraken\Loop\LoopInterface;
use Kraken\Promise\PromiseInterface;
use Exception;

class Simulation
{
    /**
     * @var LoopExtendedInterface
     */
    private $loop;

    /**
     * @var callable(Simulation)
     */
    private $scenario;

    /**
     * @var
     */
    private $successEvents;

    /**
     * @var string|null
     */
    private $failureMessage;

    /**
     * @var callable(Simulation)
     */
    private $startCallback;

    /**
     * @var callable(Simulation)
     */
    private $stopCallback;

    /**
     * @param callable(Simulation)|null $scenario
     */
    public function __construct(callable $scenario = null)
    {
        $this->scenario = $scenario !== null ? $scenario : function() {};
        $this->successEvents = new EventCollection();
        $this->failureMessage = null;
        $this->startCallback = function() {};
        $this->stopCallback = function() {};

        $this->create();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->stop();
        $this->destroy();

        unset($this->scenario);
        unset($this->successEvents);
        unset($this->failureMessage);
        unset($this->startCallback);
        unset($this->stopCallback);
    }

    /**
     * @param callable(Simulation) $scenario
     */
    public function setScenario(callable $scenario)
    {
        $this->scenario = $scenario;
    }

    /**
     * @return callable(Simulation)|null $scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     *
     */
    public function begin()
    {
        $this->start();
    }

    /**
     *
     */
    public function done()
    {
        $this->stop();
    }

    /**
     * @return PromiseInterface
     */
    public function fail($message)
    {
        $this->failureMessage = $message;
        $this->stop();
    }

    /**
     * @param string $name
     * @param mixed $data
     */
    public function expectEvent($name, $data = [])
    {
        $this->successEvents->enqueue(new Event($name, $data));
    }

    /**
     * @return EventCollection
     */
    public function getExpectedEvents()
    {
        return $this->successEvents;
    }

    /**
     * @param callable $callable
     */
    public function onStart(callable $callable)
    {
        $this->startCallback = $callable;
    }

    /**
     * @param callable $callable
     */
    public function onStop(callable $callable)
    {
        $this->stopCallback = $callable;
    }

    /**
     * @throws Exception
     */
    private function start()
    {
        $sim = $this;

        $scenario = $this->scenario;
        $scenario($sim);

        $callable = $this->startCallback;
        $callable($sim);

        $loop = $this->loop;
        $loop->addTimer(15, function() use($sim) {
            $sim->fail('Timeout for test has been reached.');
        });

        $loop->start();

        if ($sim->failureMessage !== null)
        {
            throw new Exception($sim->failureMessage);
        }
    }

    /**
     *
     */
    private function stop()
    {
        if ($this->loop !== null && $this->loop->isRunning())
        {
            $this->loop->stop();
            $this->loop->flush(true);

            $callable = $this->stopCallback;
            $callable($this);
        }
    }

    /**
     *
     */
    private function create()
    {
        $this->loop = new Loop(new StreamSelectLoop);
        $this->loop->flush(true);
    }

    /**
     *
     */
    private function destroy()
    {
        unset($this->loop);
    }
}
