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
     * @return mixed[]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @param callable $factoryMethod
     * @return FactoryInterface
     */
    public function define($name, callable $factoryMethod)
    {
        $this->definitions[$name] = $factoryMethod;

        return $this;
    }

    /**
     * @param string $name
     * @return FactoryInterface
     */
    public function remove($name)
    {
        unset($this->definitions[$name]);

        return $this;
    }

    /**
     * @param string $name
     * @param mixed[]|mixed $args
     * @return mixed
     * @throws IllegalCallException
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
     * @param callable[] $factoryMethods
     */
    public function addDefinitions($factoryMethods)
    {
        foreach ($factoryMethods as $name=>$factoryMethod)
        {
            $this->define($name, $factoryMethod);
        }
    }

    /**
     * @return callable[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
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
