<?php

namespace Kraken\Error;

use Exception;
use Kraken\Promise\PromiseInterface;
use Kraken\Exception\Runtime\ExecutionException;
use Kraken\Exception\Runtime\IllegalCallException;
use Kraken\Exception\Runtime\LogicException;

interface ErrorManagerInterface
{
    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function __invoke(Exception $ex, $params = []);

    /**
     * @param string $key
     * @return bool
     */
    public function existsParam($key);

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setParam($key, $value);

    /**
     * @param string $key
     * @return mixed|null $value
     */
    public function getParam($key);

    /**
     * @param string $exception
     * @return bool
     */
    public function existsHandler($exception);

    /**
     * @param string $exception
     * @param ErrorHandlerInterface|string|string[] $handler
     * @throws IllegalCallException
     * @throws LogicException
     */
    public function setHandler($exception, $handler);

    /**
     * @param string $exception
     * @return ErrorHandlerInterface|null
     */
    public function getHandler($exception);

    /**
     * @param string $exception
     */
    public function removeHandler($exception);

    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     * @throws ExecutionException
     */
    public function handle(Exception $ex, $params = []);
}
