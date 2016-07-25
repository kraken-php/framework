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
     * @return PromiseInterface
     */
    public function promise()
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
     * @param mixed|null $value
     * @return PromiseInterface
     */
    public function resolve($value = null)
    {
        $this->promise();

        return call_user_func($this->resolveCallback, $value);
    }

    /**
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function reject($reason = null)
    {
        $this->promise();

        return call_user_func($this->rejectCallback, $reason);
    }

    /**
     * @param Error|Exception|string|null $reason
     * @return PromiseInterface
     */
    public function cancel($reason = null)
    {
        $this->promise();

        return call_user_func($this->cancelCallback, $reason);
    }
}
