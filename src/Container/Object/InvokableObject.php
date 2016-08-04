<?php

namespace Kraken\Container\Object;

class InvokableObject
{
    /**
     * @var object
     */
    protected $obj;

    /**
     * @param object $obj
     */
    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->obj);
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->obj;
    }
}
