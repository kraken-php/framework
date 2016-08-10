<?php

namespace Kraken\Supervisor;

use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Error;
use Exception;

interface SupervisorInterface
{
    /**
     * Handle given Error or Exception with set of params using solver's handler method.
     *
     * @see SupervisorInterface::handle
     *
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
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
     * Check if handler for $exception does exist.
     *
     * @param string $exception
     * @return bool
     */
    public function existsHandler($exception);

    /**
     * Set handler for $exception.
     *
     * IllegalCallException is thrown if given $handler cannot be resolved.
     *
     * @param string $exception
     * @param SolverInterface|string|string[] $handler
     * @throws IllegalCallException
     */
    public function setHandler($exception, $handler);

    /**
     * Return handler for $exception or null if it does not exist.
     *
     * @param string $exception
     * @return SolverInterface|null
     */
    public function getHandler($exception);

    /**
     * Remove handler for $exception.
     *
     * @param string $exception
     */
    public function removeHandler($exception);

    /**
     * Handle error or exception of $ex using known handlers.
     *
     * Returns FulfilledPromise if handler for exception does exist and was executed properly. If handler does not exist
     * or other errors and/or exception were thrown then RejectedPromise is returned.
     *
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function handle($ex, $params = []);
}
