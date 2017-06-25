<?php

namespace Kraken\Runtime\Container\Process;

use Composer\Autoload\ClassLoader;
use Dazzle\Loop\Flow\FlowController;

class ProcessController extends FlowController
{
    /**
     * @var ClassLoader
     */
    public $loader;

    /**
     * @param ClassLoader $loader
     */
    public function __construct(ClassLoader $loader)
    {
        parent::__construct();

        $this->loader = $loader;
        $this->isRunning = true;
    }
}
