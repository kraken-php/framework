<?php

namespace Kraken\Io\Http;

use Kraken\Io\Http\Driver\HttpDriverInterface;
use Kraken\Io\IoServerComponentInterface;

interface HttpServerInterface extends IoServerComponentInterface
{
    /**
     * Return current driver
     *
     * @return HttpDriverInterface
     */
    public function getDriver();
}
