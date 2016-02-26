<?php

namespace Kraken\Util\Factory;

use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\IllegalFieldException;

trait SimpleFactoryTrait
{
    /**
     * @var mixed[]
     */
    protected $params;

    /**
     * @var callable|null
     */
    protected $definition;

    /**
     *
     */
    public function __construct()
    {
        $this->params = [];
        $this->definition = null;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->params);
        unset($this->definition);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return FactoryInterface
     */
    public function bindParam($name, $value)
    {
        $this->params[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return FactoryInterface
     */
    public function unbindParam($name, $value)
    {
        unset($this->params[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws IllegalFieldException
     */
    public function getParam($name)
    {
        if (!isset($this->params[$name]))
        {
            throw new IllegalFieldException("Factory does not posses param [$name].");
        }

        return $this->invoke($this->params[$name]);
    }

    /**
     * @param string $param
     * @return bool
     */
    public function hasParam($param)
    {
        return isset($this->params[$param]);
    }

    /**
     * @param callable $factoryMethod
     * @return FactoryInterface
     */
    public function define(callable $factoryMethod)
    {
        $this->definition = $factoryMethod;

        return $this;
    }

    /**
     * @param mixed[]|mixed $args
     * @return mixed
     * @throws IllegalCallException
     */
    public function create($args = [])
    {
        if ($this->definition === null)
        {
            throw new IllegalCallException("Factory does not posses required definition.");
        }

        return call_user_func_array($this->definition, (array) $args);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function invoke($value)
    {
        return is_callable($value) ? $value() : $value;
    }
}
