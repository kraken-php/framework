<?php

namespace Kraken\Supervision;

use Kraken\Promise\Promise;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Error;
use Exception;

class SolverComposite implements SolverInterface
{
    /**
     * @var SolverInterface[]
     */
    protected $handlers;

    /**
     * @param SolverInterface[] $handlers
     */
    public function __construct($handlers = [])
    {
        $this->handlers = $handlers;

        $this->construct();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->destruct();

        unset($this->handlers);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function __invoke($ex, $params = [])
    {
        return $this->solve($ex, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function solve($ex, $params = [])
    {
        return Promise::doResolve($this->solver($ex, $params));
    }

    /**
     * Handler to be called when solver is requested.
     *
     * @param Error|Exception|string $ex
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function solver($ex, $params = [])
    {
        $promise = Promise::doResolve();

        foreach ($this->handlers as $handler)
        {
            $current = $handler;

            $promise = $promise->then(
                function() use($ex, $params, $current) {
                    return Promise::doResolve($current->solve($ex, $params));
                }
            );
        }

        return $promise;
    }

    /**
     * Pseudo-Constructor.
     */
    protected function construct()
    {}

    /**
     * Pseudo-Destructor.
     */
    protected function destruct()
    {}
}
