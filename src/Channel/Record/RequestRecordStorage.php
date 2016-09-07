<?php

namespace Kraken\Channel\Record;

use Kraken\Util\Support\TimeSupport;
use Kraken\Throwable\Exception\Runtime\TimeoutException;
use Kraken\Throwable\ThrowableProxy;
use Error;
use Exception;

trait RequestRecordStorage
{
    /**
     * @var RequestRecord[]
     */
    protected $reqs = [];

    /**
     * Create RequestRecord.
     *
     * @param string $pid
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return RequestRecord
     */
    protected function createRequest($pid, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        if ($timeout > 0.0)
        {
            $timeout = $timeout * 1000 + TimeSupport::now();
        }

        return new RequestRecord($pid, $success, $failure, $cancel, $timeout);
    }

    /**
     * Check if RequestRecord with given protocol ID exists.
     *
     * @param string $pid
     * @return bool
     */
    protected function existsRequest($pid)
    {
        return isset($this->reqs[$pid]);
    }

    /**
     * Add new RequestRecord to storage.
     *
     * @param string $pid
     * @param RequestRecord $request
     */
    protected function addRequest($pid, RequestRecord $request)
    {
        $this->reqs[$pid] = $request;
    }

    /**
     * Return RequestRecord if it exists or null if it does not exist.
     *
     * @param string $pid
     * @return RequestRecord|null
     */
    protected function getRequest($pid)
    {
        return $this->reqs[$pid];
    }

    /**
     * Resolve RequestRecord if it exists that has protocol ID equal to $pid.
     *
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
     * Reject RequestRecord if it exists that has protocol ID equal to $pid.
     *
     * @param string $pid
     * @param Error|Exception|ThrowableProxy $ex
     */
    protected function rejectRequest($pid, $ex)
    {
        $callback = $this->reqs[$pid]->onFailure();
        unset($this->reqs[$pid]);
        $callback($ex);
    }

    /**
     * Cancel RequestRecord if it exists that has protocol ID equal to $pid.
     *
     * @param string $pid
     * @param Error|Exception|ThrowableProxy $ex
     */
    protected function cancelRequest($pid, $ex)
    {
        $callback = $this->reqs[$pid]->onCancel();
        unset($this->reqs[$pid]);
        $callback($ex);
    }

    /**
     * Cancel overdue Requests.
     */
    protected function expireRequests()
    {
        $now = TimeSupport::now();
        $expiredReqs = [];

        foreach ($this->reqs as $pid=>$request)
        {
            if ($now >= $request->getTimeout())
            {
                $expiredReqs[] = $request;
            }
        }

        foreach ($expiredReqs as $request)
        {
            unset($this->reqs[$request->getPid()]);
        }

        foreach ($expiredReqs as $request)
        {
            $request->cancel(new ThrowableProxy(new TimeoutException("RequestRecord has expired.")));
        }
    }
}
