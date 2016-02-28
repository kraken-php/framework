<?php

namespace Kraken\Supervisor;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Logic\IllegalCallException;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use Kraken\Runtime\RuntimeInterface;
use Error;
use Exception;

class SolverBase implements SolverInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @var mixed[]
     */
    protected $context;

    /**
     * @var string[]
     */
    protected $requires;

    /**
     * @param RuntimeInterface $runtime
     * @param mixed[] $context
     */
    public function __construct(RuntimeInterface $runtime, $context = [])
    {
        $this->runtime = $runtime;
        $this->context = $context;
        $this->requires = [];

        $this->construct();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->destruct();

        unset($this->runtime);
        unset($this->context);
        unset($this->requires);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function __invoke($ex, $params = [])
    {
        return $this->handle($ex, $params);
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function handle($ex, $params = [])
    {
        foreach ($this->requires as $require)
        {
            if (!isset($params[$require]))
            {
                return Promise::doReject(
                    new IllegalCallException('Missing parameter [' . $require . '] for [' . get_class($this) . '].')
                );
            }
        }

        return Promise::doResolve([ $ex, $params ])
            ->spread(function($ex, $params) {
                return $this->handler($ex, $params);
            });
    }

    /**
     * @param Error|Exception $ex
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function handler($ex, $params = [])
    {
        throw new RejectionException('[' . get_class($this) . '] code undefined.');
    }

    /**
     *
     */
    protected function construct()
    {}

    /**
     *
     */
    protected function destruct()
    {}
}
