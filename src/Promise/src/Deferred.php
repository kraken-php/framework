<?php

namespace Kraken\Promise;

use Error;
use Exception;

class Deferred implements DeferredInterface
{
    /**
     * @var PromiseInterface|null
     */
    protected $promise;

    /**
     * @var callable
     */
    protected $resolveCallback;

    /**
     * @var callable
     */
    protected $rejectCallback;

    /**
     * @var callable
     */
    protected $cancelCallback;

    /**
     * @var callable|null
     */
    protected $canceller;

    /**
     * @param callable $canceller
     */
    public function __construct($canceller = null)
    {
        $this->promise = null;
        $this->resolveCallback = function($value = null) {};
        $this->rejectCallback = function($reason = null) {};
        $this->cancelCallback = function($reason = null) {};
        $this->canceller = $canceller;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->promise);
        unset($this->resolveCallback);
        unset($this->rejectCallback);
        unset($this->cancelCallback);
        unset($this->canceller);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getPromise()
    {
        if (null === $this->promise)
        {
            $this->promise = new Promise(function($resolve, $reject, $cancel) {
                $this->resolveCallback = $resolve;
                $this->rejectCallback  = $reject;
                $this->cancelCallback  = $cancel;
            }, $this->canceller);
        }

        return $this->promise;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resolve($value = null)
    {
        $this->getPromise();

        return call_user_func($this->resolveCallback, $value);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function reject($reason = null)
    {
        $this->getPromise();

        return call_user_func($this->rejectCallback, $reason);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function cancel($reason = null)
    {
        $this->getPromise();

        return call_user_func($this->cancelCallback, $reason);
    }
}
