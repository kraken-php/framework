<?php

namespace Kraken\Log\Handler;

use Monolog\Handler\HandlerInterface as MonologHandlerInterface;

interface HandlerInterface extends MonologHandlerInterface
{
    /**
     * @return MonologHandlerInterface
     */
    public function getModel();
}
