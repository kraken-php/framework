<?php

namespace Kraken\Container\Method;

class FactoryMethod
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var mixed[]
     */
    protected $args;

    /**
     * @param callable $callback
     * @param mixed[] $args
     */
    public function __construct(callable $callback, $args = [])
    {
        $this->callback = $callback;
        $this->args = $args;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return mixed[]
     */
    public function getArgs()
    {
        return $this->args;
    }
}

