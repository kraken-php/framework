<?php

namespace Kraken\Runtime;

use Kraken\Core\CoreGetterAwareInterface;
use Dazzle\Event\EventEmitterInterface;
use Dazzle\Event\EventListener;
use Dazzle\Loop\LoopGetterAwareInterface;
use Kraken\Promise\PromiseInterface;
use Error;
use Exception;

/**
 * @event beforeCreate  : callable()
 * @event create        : callable()
 * @event afterCreate   : callable()
 * @event beforeDestroy : callable()
 * @event destroy       : callable()
 * @event afterDestroy  : callable()
 * @event beforeStart   : callable()
 * @event start         : callable()
 * @event afterStart    : callable()
 * @event beforeStop    : callable()
 * @event stop          : callable()
 * @event afterStop     : callable()
 */
interface RuntimeContainerInterface extends RuntimeContextInterface, CoreGetterAwareInterface, EventEmitterInterface,
    LoopGetterAwareInterface
{
    /**
     * Return model on which container is working
     *
     * @return RuntimeModelInterface
     */
    public function getModel();

    /**
     * Return runtime manager set to this container instance.
     *
     * @return RuntimeManagerInterface
     */
    public function getManager();

    /**
     * Return current state of the container.
     *
     * Returned value might be one of:
     * Runtime::STATE_CREATED
     * Runtime::STATE_STARTED
     * Runtime::STATE_STOPPED
     * Runtime::STATE_DESTROYED
     * Runtime::STATE_FAILED
     *
     * @return int
     */
    public function getState();

    /**
     * Return failure hash if the container is in the failed state or null if it is not.
     *
     * @return string|null
     */
    public function getHash();

    /**
     * Attach beforeCreate event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onBeforeCreate(callable $callback);

    /**
     * Attach create event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onCreate(callable $callback);

    /**
     * Attach afterCreate event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onAfterCreate(callable $callback);

    /**
     * Attach beforeDestroy event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onBeforeDestroy(callable $callback);

    /**
     * Attach destroy event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onDestroy(callable $callback);

    /**
     * Attach afterDestroy event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onAfterDestroy(callable $callback);

    /**
     * Attach beforeStart event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onBeforeStart(callable $callback);

    /**
     * Attach start event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onStart(callable $callback);

    /**
     * Attach afterStart event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onAfterStart(callable $callback);

    /**
     * Attach beforeStop event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onBeforeStop(callable $callback);

    /**
     * Attach stop event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onStop(callable $callback);

    /**
     * Attach afterStop event handler.
     *
     * @param callable $callback
     * @return EventListener
     */
    public function onAfterStop(callable $callback);

    /**
     * Check if container is in created state.
     *
     * @return bool
     */
    public function isCreated();

    /**
     * Check if container is in destroyed state.
     *
     * @return bool
     */
    public function isDestroyed();

    /**
     * Check if container is in started state.
     *
     * @return bool
     */
    public function isStarted();

    /**
     * Check if container is in stopped state.
     *
     * @return bool
     */
    public function isStopped();

    /**
     * Check if container is in failed state.
     *
     * @return bool
     */
    public function isFailed();

    /**
     * Create container.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function create();

    /**
     * Destroy container.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function destroy();

    /**
     * Start container.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function start();

    /**
     * Stop container.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function stop();

    /**
     * Temporarily switch container to failed workflow and allow supervisor to take control.
     *
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @throws Exception
     */
    public function fail($ex, $params = []);

    /**
     * Switch back from failed to normal workflow.
     */
    public function succeed();
}
