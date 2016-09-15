<?php

namespace Kraken\_Module\Runtime\_Mock\Supervision;

use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Runtime\RuntimeModelInterface;
use Kraken\Supervision\Solver;
use Kraken\Supervision\SolverInterface;
use Error;
use Exception;

class ModelContinue extends Solver implements SolverInterface
{
    /**
     * @var RuntimeModelInterface|RuntimeContainerInterface
     */
    protected $model;

    /**
     * @var string[]
     */
    protected $queue;

    /**
     * @param mixed[] $context
     */
    public function __construct($context = [])
    {
        $this->model =  $context['model'];
        $this->queue =& $context['queue'];

        parent::__construct($context);
    }

    /**
     *
     */
    public function __destruct()
    {
        parent::__destruct();

        unset($this->model);
        unset($this->queue);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     */
    protected function solver($ex, $params = [])
    {
        $model = $this->model;
        $loop  = $model->getLoop();

        $loop->onTick(function() use($model) {
            $this->queue[] = 'C';
            $model->succeed();
        });
    }
}
