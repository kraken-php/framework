<?php

namespace Kraken\_Unit\Filesystem\_Mock;

class FilesystemAdapterFactoryMock
{
    /**
     * @var mixed[]
     */
    private $args;

    /**
     *
     */
    public function __construct()
    {
        $args = func_get_args();

        foreach ($args as $key=>$arg)
        {
            if (is_object($arg))
            {
                $args[$key] = get_class($arg);
            }
        }

        $this->args = $args;
    }

    /**
     *
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     *
     */
    public function __call($method, $args = [])
    {
        return $this;
    }
}
