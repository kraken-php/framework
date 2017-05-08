<?php

namespace Kraken\Util\Factory;

use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\IllegalFieldException;

interface FactoryInterface
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
     * Define factory method for some object and register in under $name key.
     *
     * @param string $name
     * @param callable $factoryMethod
     * @return FactoryInterface
     */
    public function define($name, callable $factoryMethod);

    /**
     * Remove factory method definition stored under $name key.
     *
     * @param string $name
     * @return FactoryInterface
     */
    public function remove($name);

    /**
     * Return factory method definition stored under $name key.
     *
     * @param string $name
     * @return callable
     * @throws IllegalFieldException
     */
    public function getDefinition($name);

    /**
     * Check if there is any factory method definition stored under $name key.
     *
     * @param string $name
     * @return bool
     */
    public function hasDefinition($name);

    /**
     * Return all definitions stored in Factory in the form of associative array.
     *
     * @return callable[]
     */
    public function getDefinitions();

    /**
     * Create object with given arguments using factoryMethod stored under $name key.
     *
     * Create object with given arguments using factoryMethod stored under $name key. Throws exception if there is no
     * definition stored.
     *
     * @param string $name
     * @param mixed[] $args
     * @return mixed
     * @throws IllegalCallException
     */
    public function create($name, $args = []);
}
