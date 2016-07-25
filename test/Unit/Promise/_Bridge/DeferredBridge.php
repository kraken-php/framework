<?php

namespace Kraken\Test\Unit\Promise\_Bridge;

use Kraken\Promise\DeferredInterface;

class DeferredBridge implements DeferredInterface
{
    /**
     * @var callable[]
     */
    private $callbacks;

    /**
     * @param callable[] $callbacks
     */
    public function __construct($callbacks)
    {
        $this->callbacks = $callbacks;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->callbacks);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getPromise()
    {
        return call_user_func_array($this->callbacks['getPromise'], func_get_args());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resolve($value = null)
    {
        return call_user_func_array($this->callbacks['resolve'], func_get_args());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function reject($reason = null)
    {
        return call_user_func_array($this->callbacks['reject'], func_get_args());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function cancel($reason = null)
    {
        return call_user_func_array($this->callbacks['cancel'], func_get_args());
    }
}
