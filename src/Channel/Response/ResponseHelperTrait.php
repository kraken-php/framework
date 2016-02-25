<?php

namespace Kraken\Channel\Response;

use Kraken\Support\TimeSupport;

trait ResponseHelperTrait
{
    /**
     * @var Response[]
     */
    protected $reps = [];

    /**
     * @var Response[]
     */
    protected $handledReps = [];

    /**
     * @var int
     */
    protected $handledRepsTimeout = 0;

    /**
     * @param string $pid
     * @param string $alias
     * @param float $timeout
     * @param float $timeoutIncrease
     * @return Response
     */
    protected function createResponse($pid, $alias, $timeout = 0.0, $timeoutIncrease = 1.0)
    {
        return new Response($pid, $alias, $timeout, $timeoutIncrease);
    }

    /**
     * @param $pid
     * @return bool
     */
    protected function existsResponse($pid)
    {
        return isset($this->reps[$pid]) || isset($this->handledReps[$pid]);
    }

    /**
     * @param string $pid
     * @param Response $response
     */
    protected function addResponse($pid, Response $response)
    {
        $this->reps[$pid] = $response;
    }

    /**
     * @param string $pid
     * @return Response
     */
    protected function getResponse($pid)
    {
        return $this->reps[$pid];
    }

    /**
     * @param string $pid
     * @param $exception
     */
    protected function resolveOrRejectResponse($pid, $exception)
    {
        if ($exception !== 'Kraken\Throwable\System\TaskUnfinishedException')
        {
            unset($this->reps[$pid]);
            $this->handledReps[$pid] = new Response($pid, '', TimeSupport::now() + $this->handledRepsTimeout);
        }
    }

    /**
     * @return Response[]
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
     *
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
