<?php

namespace Kraken\Transfer\Http;

use Kraken\Transfer\Http\Driver\HttpDriverInterface;
use Kraken\Transfer\ServerComponentInterface;

interface HttpServerInterface extends ServerComponentInterface
{
    /**
     * Return current driver.
     *
     * @return HttpDriverInterface
     */
    public function getDriver();
}
