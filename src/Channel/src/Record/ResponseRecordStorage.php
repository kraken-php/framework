<?php

namespace Kraken\Channel\Record;

use Kraken\Util\Support\TimeSupport;
use Dazzle\Throwable\Exception\System\TaskIncompleteException;

trait ResponseRecordStorage
{
    /**
     * @var ResponseRecord[]
     */
    protected $reps = [];

    /**
     * @var ResponseRecord[]
     */
    protected $handledReps = [];

    /**
     * @var int
     */
    protected $handledRepsTimeout = 0;

    /**
     * Create ResponseRecord.
     *
     * @param string $pid
     * @param string $alias
     * @param float $timeout
     * @param float $timeoutIncrease
     * @return ResponseRecord
     */
    protected function createResponse($pid, $alias, $timeout = 0.0, $timeoutIncrease = 1.0)
    {
        return new ResponseRecord($pid, $alias, $timeout, $timeoutIncrease);
    }

    /**
     * Check if ResponseRecord with given protocol ID exists.
     *
     * @param $pid
     * @return bool
     */
    protected function existsResponse($pid)
    {
        return isset($this->reps[$pid]) || isset($this->handledReps[$pid]);
    }

    /**
     * Add new ResponseRecord to storage.
     *
     * @param string $pid
     * @param ResponseRecord $response
     */
    protected function addResponse($pid, ResponseRecord $response)
    {
        $this->reps[$pid] = $response;
    }

    /**
     * Return ResponseRecord if it exists or null if it does not exist.
     *
     * @param string $pid
     * @return ResponseRecord
     */
    protected function getResponse($pid)
    {
        return $this->reps[$pid];
    }

    /**
     * Mark ResponseRecord as handled if it exists that and has protocol ID equal to $pid.
     *
     * @param string $pid
     * @param $exception
     */
    protected function resolveOrRejectResponse($pid, $exception)
    {
        if ($exception !== TaskIncompleteException::class)
        {
            unset($this->reps[$pid]);
            $this->handledReps[$pid] = new ResponseRecord($pid, '', TimeSupport::now() + $this->handledRepsTimeout);
        }
    }

    /**
     * Return all unhandled ResponseRecords in array form.
     *
     * @return ResponseRecord[]
     */
    protected function unfinishedResponses()
    {
        $now = TimeSupport::now();
        $unfinishedReps = [];

        foreach ($this->reps as $pid=>$response)
        {
            if ($now >= $response->timeout)
            {
                $unfinishedReps[] = $response;
                $response->timeout = $now + $response->timeoutIncrease;
            }
        }

        return $unfinishedReps;
    }

    /**
     * Expire unhandled ResponseRecords.
     */
    protected function expireResponses()
    {
        $now = TimeSupport::now();
        $expiredReps = [];

        foreach ($this->handledReps as $pid=>$response)
        {
            if ($now >= $response->timeout)
            {
                $expiredReps[] = $pid;
            }
        }

        foreach ($expiredReps as $pid)
        {
            unset($this->handledReps[$pid]);
        }
    }
}
