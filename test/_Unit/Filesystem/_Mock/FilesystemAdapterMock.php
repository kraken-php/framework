<?php

namespace Kraken\_Unit\Filesystem\_Mock;

class FilesystemAdapterMock
{
    /**
     * @var mixed[]
     */
    private $args;

    /**
     *
     */
    public function __construct($config = [])
    {
        $this->args = [];
        $this->args[] = $config;
    }

    /**
     *
     */
    public function getArgs()
    {
        return $this->args;
    }
}
