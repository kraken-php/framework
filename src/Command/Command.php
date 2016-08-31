<?php

namespace Kraken\Command;

use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Throwable\Exception\Runtime\RejectionException;

class Command implements CommandInterface
{
    /**
     * @var mixed[]
     */
    protected $context;

    /**
     * @param mixed[] $context
     */
    public function __construct($context = [])
    {
        $this->context = $context;

        $this->construct();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->destruct();

        unset($this->context);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function __invoke($params = [])
    {
        return $this->execute($params);
    }

    /**
     * @override
     * @inheritDoc
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
