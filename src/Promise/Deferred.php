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
     * @var callable
     */
    protected $notifyCallback;

    /**
     *
     */
    public function __construct()
    {
        $this->promise = null;
        $this->resolveCallback = function($value = null) {};
        $this->rejectCallback = function($reason = null) {};
        $this->cancelCallback = function($reason = null) {};
        $this->notifyCallback = function($update = null) {};
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
        unset($this->notifyCallback);
    }

    /**
     * @return PromiseInterface
     */
    public function promise()
    {
        if (null === $this->promise)
        {
            $this->promise = new Promise(function($resolve, $reject, $cancel, $notify) {
                $this->resolveCallback = $resolve;
                $this->rejectCallback  = $reject;
                $this->cancelCallback  = $cancel;
                $this->notifyCallback  = $notify;
            });
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

    /**
     * @param mixed|null $update
     * @return PromiseInterface
     */
    public function notify($update = null)
    {
        $this->promise();

        return call_user_func($this->notifyCallback, $update);
    }
}
