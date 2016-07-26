<?php

namespace Kraken\Test\Stub;

use Kraken\Loop\LoopInterface;
use Kraken\Promise\PromiseInterface;

interface SimulationInterface
{
    /**
     * @return LoopInterface
     */
    public function getLoop();

    /**
     *
     */
    public function done();

    /**
     * @param string $message
     * @return PromiseInterface
     */
    public function fail($message);

    /**
     * @param string $name
     * @param mixed $data
     */
    public function expectEvent($name, $data = []);

    /**
     * @param callable $callable
     */
    public function onStart(callable $callable);

    /**
     * @param callable $callable
     */
    public function onStop(callable $callable);

    /**
     * @param string $model
     * @param mixed[] $config
     * @return object
     */
    public function reflect($model, $config = []);
}
