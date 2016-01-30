<?php

namespace Kraken\Error;

use Exception;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Exception\Runtime\MissingFieldException;
use Kraken\Exception\Runtime\RejectionException;
use Kraken\Runtime\RuntimeInterface;

class ErrorHandlerBase implements ErrorHandlerInterface
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
     * @param Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function __invoke(Exception $ex, $params = [])
    {
        return $this->handle($ex, $params);
    }

    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function handle(Exception $ex, $params = [])
    {
        foreach ($this->requires as $require)
        {
            if (!isset($params[$require]))
            {
                return Promise::doReject(
                    new MissingFieldException('Missing parameter [' . $require . '] for [' . get_class($this) . '].')
                );
            }
        }

        return Promise::doResolve([ $ex, $params ])
            ->spread(function($ex, $params) {
                return $this->handler($ex, $params);
            });
    }

    /**
     * @param Exception $ex
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function handler(Exception $ex, $params = [])
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
