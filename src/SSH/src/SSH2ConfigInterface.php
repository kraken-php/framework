<?php

namespace Kraken\SSH;

interface SSH2ConfigInterface
{
    /**
     * Return connection host.
     *
     * @return string
     */
    public function getHost();

    /**
     * Return connection port.
     *
     * @return int
     */
    public function getPort();

    /**
     * Return connection configuration methods.
     *
     * @return mixed
     */
    public function getMethods();
}
