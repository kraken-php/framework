<?php

namespace Kraken\Runtime;

use Exception;
use Kraken\Core\CoreAwareInterface;
use Kraken\Core\CoreInputContextInterface;
use Kraken\Error\ErrorManagerAwareInterface;
use Kraken\Event\EventEmitterAwareInterface;
use Kraken\Loop\LoopExtendedAwareInterface;
use Kraken\Promise\PromiseInterface;

interface RuntimeModelInterface extends
    CoreAwareInterface,
    CoreInputContextInterface,
    ErrorManagerAwareInterface,
    EventEmitterAwareInterface,
    LoopExtendedAwareInterface,
    RuntimeManagerAwareInterface
{
    /**
     * @param int $state
     */
    public function setState($state);

    /**
     * @return int
     */
    public function getState();

    /**
     * @return int
     */
    public function state();

    /**
     * @param int $state
     * @return bool
     */
    public function isState($state);

    /**
     * @return bool
     */
    public function isCreated();

    /**
     * @return bool
     */
    public function isDestroyed();

    /**
     * @return bool
     */
    public function isStarted();

    /**
     * @return bool
     */
    public function isStopped();

    /**
     * @return PromiseInterface
     */
    public function create();

    /**
     * @return PromiseInterface
     */
    public function destroy();

    /**
     * @return PromiseInterface
     */
    public function start();

    /**
     * @return PromiseInterface
     */
    public function stop();

    /**
     * @param Exception $ex
     * @param mixed[] $params
     */
    public function fail(Exception $ex, $params = []);

    /**
     *
     */
    public function succeed();
}
