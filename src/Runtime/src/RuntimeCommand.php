<?php

namespace Kraken\Runtime;

class RuntimeCommand
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var string[]
     */
    protected $params;

    /**
     * @param string $command
     * @param string[] $params
     */
    public function __construct($command, $params = [])
    {
        $this->command = $command;
        $this->params = $params;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->command);
        unset($this->params);
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return string[]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode(array_merge([ $this->command ], $this->params));
    }
}
