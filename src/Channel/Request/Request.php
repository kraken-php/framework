<?php

namespace Kraken\Channel\Request;

use Kraken\Throwable\ThrowableProxy;
use Error;
use Exception;

class Request
{
    /**
     * @var string
     */
    public $pid;

    /**
     * @var callable
     */
    public $success;

    /**
     * @var callable
     */
    public $failure;

    /**
     * @var callable
     */
    public $cancel;

    /**
     * @var float
     */
    public $timeout;

    /**
     * @param string $pid
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     */
    public function __construct($pid, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        $this->pid     = $pid;
        $this->success = ($success !== null) ? $success : function() {};
        $this->failure = ($failure !== null) ? $failure : function() {};
        $this->cancel  = ($cancel !== null)  ? $cancel  : function() {};
        $this->timeout = $timeout;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->pid);
        unset($this->success);
        unset($this->failure);
        unset($this->cancel);
        unset($this->timeout);
    }

    /**
     * Return protocol ID.
     *
     * @return string
     */
    public function pid()
    {
        return $this->pid;
    }

    /**
     * Return success handler.
     *
     * @return callable
     */
    public function onSuccess()
    {
        return $this->success;
    }

    /**
     * Return failure handler.
     *
     * @return callable
     */
    public function onFailure()
    {
        return $this->failure;
    }

    /**
     * Return cancellation handler.
     *
     * @return callable
     */
    public function onCancel()
    {
        return $this->cancel;
    }

    /**
     * Return timeout.
     *
     * @return float
     */
    public function timeout()
    {
        return $this->timeout;
    }

    /**
     * Resolve Request calling success handler.
     *
     * @param mixed $value
     * @return mixed
     */
    public function resolve($value)
    {
        $callback = $this->onSuccess();
        return $callback($value);
    }

    /**
     * Reject Request calling failure handler.
     *
     * @param Error|Exception|ThrowableProxy $ex
     * @return mixed
     */
    public function reject($ex)
    {
        $callback = $this->onFailure();
        return $callback($ex);
    }

    /**
     * Cancel Request calling cancellation handler.
     *
     * @param Error|Exception|ThrowableProxy $ex
     * @return mixed
     */
    public function cancel($ex)
    {
        $callback = $this->onCancel();
        return $callback($ex);
    }
}
