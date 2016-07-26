<?php

namespace Kraken\Util\Factory;

use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\IllegalFieldException;

interface SimpleFactoryInterface
{
    /**
     * Bind param $value under $name key to Factory.
     *
     * @param string $name
     * @param mixed $value
     * @return FactoryInterface
     */
    public function bindParam($name, $value);

    /**
     * Unbind param stored under $name key from Factory.
     *
     * @param string $name
     * @return FactoryInterface
     */
    public function unbindParam($name);

    /**
     * Get param stored under $name key.
     *
     * Get param stored under $name key. Throws exception if there is no param set under $name key.
     *
     * @param string $name
     * @return mixed
     * @throws IllegalFieldException
     */
    public function getParam($name);

    /**
     * Check if there is any param under $param $key.
     *
     * @param string $param
     * @return bool
     */
    public function hasParam($param);

    /**
     * Return all params stored in Factory in the form of associative array.
     *
     * @return mixed[]
     */
    public function getParams();

    /**
     * Define factory method for some object in Factory.
     *
     * @param callable $factoryMethod
     * @return FactoryInterface
     */
    public function define(callable $factoryMethod);

    /**
     * Return factory method definition stored in Factory.
     *
     * @return callable
     * @throws IllegalFieldException
     */
    public function getDefinition();

    /**
     * Check if there is any factory method definition stored in Factory.
     *
     * @return bool
     */
    public function hasDefinition();

    /**
     * Create object with given arguments using factoryMethod stored in Factory.
     *
     * Create object with given arguments using factoryMethod stored in Factory. Throws exception if there is no
     * definition stored.
     *
     * @param mixed[] $args
     * @return mixed
     * @throws IllegalCallException
     */
    public function create($args = []);
}
