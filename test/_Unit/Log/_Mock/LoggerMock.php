<?php

namespace Kraken\_Unit\Log\_Mock;

use Kraken\Log\Logger;
use Kraken\Log\LoggerWrapper;

class LoggerMock extends Logger
{
    /**
     * @param LoggerWrapper $wrapper
     */
    public function setWrapper(LoggerWrapper $wrapper)
    {
        $this->logger = $wrapper;
    }
}
