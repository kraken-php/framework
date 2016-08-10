<?php

namespace Kraken\Supervisor;

use Kraken\Promise\Promise;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
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

        $this->onCreate();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->onDestroy();

        unset($this->handlers);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function __invoke($ex, $params = [])
    {
        return $this->handle($ex, $params);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handle($ex, $params = [])
    {
        return Promise::doResolve($this->handler($ex, $params));
    }

    /**
     * Handler to be called when solver is requested.
     *
     * @param Error|Exception|string $ex
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function handler($ex, $params = [])
    {
        $promise = Promise::doResolve();

        foreach ($this->handlers as $handler)
        {
            $current = $handler;

            $promise = $promise->then(
                function() use($ex, $params, $current) {
                    return Promise::doResolve($current->handle($ex, $params));
                }
            );
        }

        return $promise;
    }

    /**
     * Pseudo-Constructor.
     */
    protected function onCreate()
    {}

    /**
     * Pseudo-Destructor.
     */
    protected function onDestroy()
    {}
}
