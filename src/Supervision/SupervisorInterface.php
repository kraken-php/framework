<?php

namespace Kraken\Supervision;

use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

interface SupervisorInterface
{
    /**
     * Solve given Error or Exception with set of params using solver's handler method.
     *
     * @see SupervisorInterface::solve
     *
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     * @throws ExecutionException
     */
    public function __invoke($ex, $params = []);

    /**
     * Check if param saved under $key exists.
     *
     * @param string $key
     * @return bool
     */
    public function existsParam($key);

    /**
     * Set value of param saved under $key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setParam($key, $value);

    /**
     * Return param saved under $key or null if it does not exist.
     *
     * @param string $key
     * @return mixed|null $value
     */
    public function getParam($key);

    /**
     * Remove param saved under $key.
     *
     * @param string $key
     */
    public function removeParam($key);

    /**
     * Check if solver for $exception does exist.
     *
     * @param string $exception
     * @return bool
     */
    public function existsSolver($exception);

    /**
     * Set solver for $exception.
     *
     * IllegalCallException is thrown if given $handler cannot be resolved.
     *
     * @param string $exception
     * @param SolverInterface|string|string[] $handler
     * @throws IllegalCallException
     */
    public function setSolver($exception, $handler);

    /**
     * Return solver for $exception or null if it does not exist.
     *
     * @param string $exception
     * @return SolverInterface|null
     */
    public function getSolver($exception);

    /**
     * Remove solver for $exception.
     *
     * @param string $exception
     */
    public function removeSolver($exception);

    /**
     * Handle error or exception of $ex using known handlers.
     *
     * Returns FulfilledPromise if handler for exception does exist and was executed properly. If handler does not exist
     * or other errors and/or exception were thrown then RejectedPromise is returned.
     *
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     * @cancels Error|Exception|string|null
     */
    public function solve($ex, $params = []);
}
