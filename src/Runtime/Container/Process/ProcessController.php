<?php

namespace Kraken\Runtime\Container\Process;

use Kraken\Loop\Flow\FlowController;

class ProcessController extends FlowController
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->isRunning = true;
    }
}
