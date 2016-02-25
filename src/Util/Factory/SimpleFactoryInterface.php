<?php

namespace Kraken\Util\Factory;

use Kraken\Throwable\Runtime\IllegalCallException;
use Kraken\Throwable\Runtime\IllegalFieldException;

interface SimpleFactoryInterface
{
    /**
     * @param string $name
     * @param mixed $value
     * @return FactoryInterface
     */
    public function bindParam($name, $value);

    /**
     * @param string $name
     * @param mixed $value
     * @return FactoryInterface
     */
    public function unbindParam($name, $value);

    /**
     * @param string $name
     * @return mixed
     * @throws IllegalFieldException
     */
    public function getParam($name);

    /**
     * @param string $param
     * @return bool
     */
    public function hasParam($param);

    /**
     * @param callable $factoryMethod
     * @return FactoryInterface
     */
    public function define(callable $factoryMethod);

    /**
     * @param mixed[] $args
     * @return mixed
     * @throws IllegalCallException
     */
    public function create($args = []);
}
