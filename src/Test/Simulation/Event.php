<?php

namespace Kraken\Test\Simulation;

class Event
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed[]
     */
    protected $data;

    /**
     * @param string $name
     * @param mixed[] $data
     */
    public function __construct($name, $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return mixed[]
     */
    public function data()
    {
        return $this->data;
    }
}
