<?php

namespace Kraken\Channel\Request;

use Kraken\Exception\LazyException;
use Kraken\Exception\Runtime\TimeoutException;
use Kraken\Support\TimeSupport;
use Exception;

trait RequestHelperTrait
{
    /**
     * @var Request[]
     */
    protected $reqs = [];

    /**
     * @param string $pid
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return Request
     */
    protected function createRequest($pid, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        if ($timeout > 0.0)
        {
            $timeout = $timeout * 1000 + TimeSupport::now();
        }

        return new Request($pid, $success, $failure, $cancel, $timeout);
    }

    /**
     * @param string $pid
     * @return bool
     */
    protected function existsRequest($pid)
    {
        return isset($this->reqs[$pid]);
    }

    /**
     * @param string $pid
     * @param Request $request
     */
    protected function addRequest($pid, Request $request)
    {
        $this->reqs[$pid] = $request;
    }

    /**
     * @param string $pid
     * @return Request
     */
    protected function getRequest($pid)
    {
        return $this->reqs[$pid];
    }

    /**
     * @param string $pid
     * @param string $message
     */
    protected function resolveRequest($pid, $message)
    {
        $callback = $this->reqs[$pid]->onSuccess();
        unset($this->reqs[$pid]);
        $callback($message);
    }

    /**
     * @param string $pid
     * @param Exception|LazyException $ex
     */
    protected function rejectRequest($pid, $ex)
    {
        $callback = $this->reqs[$pid]->onFailure();
        unset($this->reqs[$pid]);
        $callback($ex);
    }

    /**
     * @param string $pid
     * @param Exception|LazyException $ex
     */
    protected function cancelRequest($pid, $ex)
    {
        $callback = $this->reqs[$pid]->onCancel();
        unset($this->reqs[$pid]);
        $callback($ex);
    }

    /**
     *
     */
    protected function expireRequests()
    {
        $now = TimeSupport::now();
        $expiredReqs = [];

        foreach ($this->reqs as $pid=>$request)
        {
            if ($now >= $request->timeout())
            {
                $expiredReqs[] = $request;
            }
        }

        foreach ($expiredReqs as $request)
        {
            unset($this->reqs[$request->pid()]);
        }

        foreach ($expiredReqs as $request)
        {
            $request->cancel(new LazyException(new TimeoutException("Request has expired.")));
        }
    }
}
