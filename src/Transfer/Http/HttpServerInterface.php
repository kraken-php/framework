<?php

namespace Kraken\Transfer\Http;

use Kraken\Transfer\Http\Driver\HttpDriverInterface;
use Kraken\Transfer\TransferComponentInterface;

interface HttpServerInterface extends TransferComponentInterface
{
    /**
     * Return current driver.
     *
     * @return HttpDriverInterface
     */
    public function getDriver();
}
