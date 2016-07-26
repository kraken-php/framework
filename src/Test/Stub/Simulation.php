<?php

namespace Kraken\Test\Stub;

use Kraken\Loop\LoopExtendedInterface;
use Kraken\Loop\LoopInterface;
use Kraken\Promise\PromiseInterface;
use Exception;
use ReflectionClass;

class Simulation implements SimulationInterface
{
    /**
     * @var LoopExtendedInterface
     */
    private $loop;

    /**
     * @var callable(SimulationInterface)
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
     * @var callable(SimulationInterface)
     */
    private $startCallback;

    /**
     * @var callable(SimulationInterface)
     */
    private $stopCallback;

    /**
     * @param LoopExtendedInterface $loop
     */
    public function __construct(LoopExtendedInterface $loop)
    {
        $this->loop = $loop;
        $this->scenario = function() {};
        $this->successEvents = new EventCollection();
        $this->failureMessage = null;
        $this->startCallback = function() {};
        $this->stopCallback = function() {};
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->stop();

        unset($loop);
        unset($this->scenario);
        unset($this->successEvents);
        unset($this->failureMessage);
        unset($this->startCallback);
        unset($this->stopCallback);
    }

    /**
     * @param callable(SimulationInterface) $scenario
     */
    public function setScenario(callable $scenario)
    {
        $this->scenario = $scenario;
    }

    /**
     * @return callable(SimulationInterface)|null $scenario
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
     * @param string $model
     * @param mixed[] $config
     * @return object
     */
    public function reflect($model, $config = [])
    {
        foreach ($config as $key=>$value)
        {
            if ($value === 'Kraken\Loop\Loop' || $value === 'Kraken\Loop\LoopInterface')
            {
                $config[$key] = $this->getLoop();
            }
        }

        return (new ReflectionClass($model))->newInstanceArgs($config);
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

            $callable = $this->stopCallback;
            $callable($this);
        }
    }
}
