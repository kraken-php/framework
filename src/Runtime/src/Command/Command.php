<?php

namespace Kraken\Runtime\Command;

use Kraken\Promise\Promise;
use Kraken\Runtime\RuntimeContainerInterface;
use Dazzle\Throwable\Exception\Logic\InstantiationException;
use Dazzle\Throwable\Exception\Runtime\RejectionException;

class Command implements CommandInterface
{
    /**
     * @var mixed[]
     */
    protected $context;

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
        if (!isset($context['runtime']) || !$context['runtime'] instanceof RuntimeContainerInterface)
        {
            throw new InstantiationException('Command did not get expected RuntimeContainerInterface.');
        }

        $this->runtime = $context['runtime'];
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
