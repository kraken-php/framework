<?php

namespace Kraken\Command;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Exception\Runtime\RejectionException;
use Kraken\Runtime\RuntimeInterface;

class Command implements CommandInterface
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
     * @param RuntimeInterface $runtime
     * @param mixed[] $context
     */
    public function __construct(RuntimeInterface $runtime, $context = [])
    {
        $this->runtime = $runtime;
        $this->context = $context;

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
    }

    /**
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function __invoke($params = [])
    {
        return $this->execute($params);
    }

    /**
     * @param mixed[] $params
     * @return PromiseInterface
     */
    public function execute($params = [])
    {
        $promise = Promise::doResolve($params);

        return $promise
            ->then(function($params) {
                return $this->command($params);
            });
    }

    /**
     * @param mixed[] $params
     * @return mixed
     * @throws RejectionException
     */
    protected function command($params = [])
    {
        throw new RejectionException('Command code undefined.');
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
