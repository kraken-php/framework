<?php

namespace Kraken\Network\Http;

use Kraken\Network\Http\Driver\HttpDriverInterface;
use Kraken\Network\ServerComponentInterface;

interface HttpServerInterface extends ServerComponentInterface
{
    /**
     * Return current driver.
     *
     * @return HttpDriverInterface
     */
    public function getDriver();
}
