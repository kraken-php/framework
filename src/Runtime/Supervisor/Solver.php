<?php

namespace Kraken\Runtime\Supervisor;

use Kraken\Supervisor\SolverInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Runtime\RuntimeContainerInterface;

class Solver extends \Kraken\Supervisor\Solver implements SolverInterface
{
    /**
     * @var RuntimeContainerInterface
     */
    protected $runtime;

    /**
     * @param mixed[] $context
     * @throws InstantiationException
     */
    public function __construct($context = [])
    {
        if (!isset($context['runtime']))
        {
            throw new InstantiationException('[' . __CLASS__ . '] could not been initialized.');
        }

        $this->runtime = $context['runtime'];
        unset($context['runtime']);

        parent::__construct($context);
    }

    /**
     *
     */
    public function __destruct()
    {
        parent::__destruct();

        unset($this->runtime);
    }
}
