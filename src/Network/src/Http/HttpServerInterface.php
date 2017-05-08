<?php

namespace Kraken\Network\Http;

use Kraken\Network\Http\Driver\HttpDriverInterface;
use Kraken\Network\NetworkComponentInterface;

interface HttpServerInterface extends NetworkComponentInterface
{
    /**
     * Return current driver.
     *
     * @return HttpDriverInterface
     */
    public function getDriver();
}
