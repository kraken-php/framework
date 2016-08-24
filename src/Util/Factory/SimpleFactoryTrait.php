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
     * @see SimpleFactoryInterface::bindParam
     */
    public function bindParam($name, $value)
    {
        $this->params[$name] = $value;

        return $this;
    }

    /**
     * @see SimpleFactoryInterface::unbindParam
     */
    public function unbindParam($name)
    {
        unset($this->params[$name]);

        return $this;
    }

    /**
     * @see SimpleFactoryInterface::getParam
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
     * @see SimpleFactoryInterface::hasParam
     */
    public function hasParam($name)
    {
        return array_key_exists($name, $this->params);
    }

    /**
     * @see SimpleFactoryInterface::getParams
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @see SimpleFactoryInterface::define
     */
    public function define(callable $factoryMethod)
    {
        $this->definition = $factoryMethod;

        return $this;
    }

    /**
     * @see SimpleFactoryInterface::getDefinition
     */
    public function getDefinition()
    {
        if (!isset($this->definition))
        {
            throw new IllegalFieldException("SimpleFactory does not posses definition.");
        }

        return $this->definition;
    }

    /**
     * @see SimpleFactoryInterface::hasDefinition
     */
    public function hasDefinition()
    {
        return isset($this->definition);
    }

    /**
     * @see SimpleFactoryInterface::create
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
    private function invoke($value)
    {
        return is_callable($value) ? $value() : $value;
    }
}
