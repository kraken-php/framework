<?php

namespace Kraken\Runtime;

use Kraken\Core\CoreGetterAwareInterface;
use Kraken\Core\CoreInputContextInterface;
use Kraken\Event\EventEmitterInterface;
use Kraken\Event\EventHandler;
use Kraken\Loop\LoopGetterAwareInterface;
use Kraken\Promise\PromiseInterface;
use Error;
use Exception;

/**
 * @event beforeCreate
 * @event create
 * @event afterCreate
 * @event beforeDestroy
 * @event destroy
 * @event afterDestroy
 * @event beforeStart
 * @event start
 * @event afterStart
 * @event beforeStop
 * @event stop
 * @event afterStop
 */
interface RuntimeInterface extends
    EventEmitterInterface,
    CoreInputContextInterface,
    CoreGetterAwareInterface,
    LoopGetterAwareInterface
{
    /**
     * @return RuntimeModelInterface
     */
    public function model();

    /**
     * @return RuntimeManagerInterface
     */
    public function manager();

    /**
     * @return int
     */
    public function state();

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onBeforeCreate(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onCreate(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onAfterCreate(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onBeforeDestroy(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onDestroy(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onAfterDestroy(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onBeforeStart(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStart(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onAfterStart(callable $callback);
    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onBeforeStop(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onStop(callable $callback);

    /**
     * @param callable $callback
     * @return EventHandler
     */
    public function onAfterStop(callable $callback);

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
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @throws Exception
     */
    public function fail($ex, $params = []);

    /**
     *
     */
    public function succeed();
}
