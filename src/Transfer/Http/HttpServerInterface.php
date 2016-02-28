<?php

namespace Kraken\Transfer\Http;

use Kraken\Transfer\Http\Driver\HttpDriverInterface;
use Kraken\Transfer\IoServerComponentInterface;

interface HttpServerInterface extends IoServerComponentInterface
{
    /**
     * Return current driver
     *
     * @return HttpDriverInterface
     */
    public function getDriver();
}
