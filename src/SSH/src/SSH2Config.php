<?php

namespace Kraken\SSH;

class SSH2Config implements SSH2ConfigInterface
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var mixed
     */
    protected $methods;

    /**
     * @param string $host
     * @param int $port
     * @param mixed $methods
     */
    public function __construct($host = 'localhost', $port = 22, $methods = [])
    {
        $this->host = $host;
        $this->port = $port;
        $this->methods = $methods;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getMethods()
    {
        return $this->methods;
    }
}
