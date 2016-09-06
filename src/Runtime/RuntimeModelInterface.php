<?php

namespace Kraken\Runtime;

use Kraken\Core\CoreAwareInterface;
use Kraken\Supervisor\SupervisorAwareInterface;
use Kraken\Event\EventEmitterAwareInterface;
use Kraken\Loop\LoopExtendedAwareInterface;
use Kraken\Promise\PromiseInterface;
use Error;
use Exception;

interface RuntimeModelInterface extends RuntimeContextInterface, RuntimeManagerAwareInterface, SupervisorAwareInterface,
    CoreAwareInterface, EventEmitterAwareInterface, LoopExtendedAwareInterface
{
    /**
     * Set state of model.
     *
     * The state might be one of:
     * Runtime::STATE_CREATED
     * Runtime::STATE_STARTED
     * Runtime::STATE_STOPPED
     * Runtime::STATE_DESTROYED
     *
     * @param int $state
     */
    public function setState($state);

    /**
     * Return state of model.
     *
     * Returned value might be one of:
     * Runtime::STATE_CREATED
     * Runtime::STATE_STARTED
     * Runtime::STATE_STOPPED
     * Runtime::STATE_DESTROYED
     *
     * @return int
     */
    public function getState();

    /**
     * @return int
     */
    public function state();

    /**
     * Checks if model is in specified state.
     *
     * State might be one of:
     * Runtime::STATE_CREATED
     * Runtime::STATE_STARTED
     * Runtime::STATE_STOPPED
     * Runtime::STATE_DESTROYED
     *
     * @param int $state
     * @return bool
     */
    public function isState($state);

    /**
     * Checks if model is in created state.
     *
     * @return bool
     */
    public function isCreated();

    /**
     * Checks if model is in destroyed state.
     *
     * @return bool
     */
    public function isDestroyed();

    /**
     * Checks if model is in started state.
     *
     * @return bool
     */
    public function isStarted();

    /**
     * Checks if model is in stopped state.
     *
     * @return bool
     */
    public function isStopped();

    /**
     * Create model.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function create();

    /**
     * Destroy model.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function destroy();

    /**
     * Start model.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function start();

    /**
     * Stop model.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function stop();

    /**
     * Temporarily switch model to failed workflow and allow supervisor to take control.
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
