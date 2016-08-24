<?php

namespace Kraken\Util\Factory;

use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Logic\IllegalFieldException;

trait FactoryTrait
{
    /**
     * @var mixed[]
     */
    protected $params;

    /**
     * @var callable[]
     */
    protected $definitions;

    /**
     *
     */
    public function __construct()
    {
        $this->params = [];
        $this->definitions = [];
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->params);
        unset($this->definitions);
    }

    /**
     * @see FactoryInterface::bindParam
     */
    public function bindParam($name, $value)
    {
        $this->params[$name] = $value;

        return $this;
    }

    /**
     * @see FactoryInterface::unbindParam
     */
    public function unbindParam($name)
    {
        unset($this->params[$name]);

        return $this;
    }

    /**
     * @see FactoryInterface::getParam
     */
    public function getParam($name)
    {
        if (!array_key_exists($name, $this->params))
        {
            throw new IllegalFieldException("Factory does not posses param [$name].");
        }

        return $this->invoke($this->params[$name]);
    }

    /**
     * @see FactoryInterface::hasParam
     */
    public function hasParam($param)
    {
        return array_key_exists($param, $this->params);
    }

    /**
     * @see FactoryInterface::getParams
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @see FactoryInterface::define
     */
    public function define($name, callable $factoryMethod)
    {
        $this->definitions[$name] = $factoryMethod;

        return $this;
    }

    /**
     * @see FactoryInterface::remove
     */
    public function remove($name)
    {
        unset($this->definitions[$name]);

        return $this;
    }

    /**
     * @see FactoryInterface::getDefinition
     */
    public function getDefinition($name)
    {
        if (!isset($this->definitions[$name]))
        {
            throw new IllegalFieldException("Factory does not posses definition [$name].");
        }

        return $this->definitions[$name];
    }

    /**
     * @see FactoryInterface::hasDefinition
     */
    public function hasDefinition($name)
    {
        return array_key_exists($name, $this->definitions);
    }

    /**
     * @see FactoryInterface::getDefinitions
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * @see FactoryInterface::create
     */
    public function create($name, $args = [])
    {
        if (!isset($this->definitions[$name]))
        {
            throw new IllegalCallException("Factory does not posses definition for [$name].");
        }

        return call_user_func_array($this->definitions[$name], (array) $args);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function invoke($value)
    {
        return is_callable($value) ? $value() : $value;
    }
}
