<?php

namespace Kraken\Channel;

use Error;
use Exception;

class ChannelRequest
{
    /**
     * @var callable
     */
    protected $success;

    /**
     * @var callable
     */
    protected $failure;

    /**
     * @var callable
     */
    protected $cancel;

    /**
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     */
    public function __construct(callable $success = null, callable $failure = null, callable $cancel = null)
    {
        $this->success = ($success !== null) ? $success : function() {};
        $this->failure = ($failure !== null) ? $failure : function() {};
        $this->cancel  = ($cancel !== null)  ? $cancel  : function() {};
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->success);
        unset($this->failure);
        unset($this->cancel);
    }

    /**
     * @return callable
     */
    public function onSuccess()
    {
        return $this->success;
    }

    /**
     * @return callable
     */
    public function onFailure()
    {
        return $this->failure;
    }

    /**
     * @return callable
     */
    public function onCancel()
    {
        return $this->cancel;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function resolve($value)
    {
        $callback = $this->onSuccess();
        return $callback($value);
    }

    /**
     * @param Error|Exception $ex
     * @return mixed
     */
    public function reject($ex)
    {
        $callback = $this->onFailure();
        return $callback($ex);
    }

    /**
     * @param Error|Exception $ex
     * @return mixed
     */
    public function cancel($ex)
    {
        $callback = $this->onCancel();
        return $callback($ex);
    }
}
