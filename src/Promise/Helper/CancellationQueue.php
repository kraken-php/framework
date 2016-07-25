<?php

namespace Kraken\Promise\Helper;

use Kraken\Promise\PromiseInterface;
use Error;
use Exception;

class CancellationQueue
{
    /**
     * @var bool
     */
    private $started = false;

    /**
     * @var PromiseInterface[]
     */
    private $queue = [];

    /**
     * @throws Error|Exception
     */
    public function __invoke()
    {
        if ($this->started)
        {
            return null;
        }

        $this->started = true;
        $this->drain();

        return null;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return count($this->queue);
    }

    /**
     * @param mixed $cancellable
     * @throws Error|Exception
     */
    public function enqueue($cancellable)
    {
        if ($cancellable instanceof PromiseInterface === false)
        {
            return;
        }

        $length = array_push($this->queue, $cancellable);

        if ($this->started && 1 === $length)
        {
            $this->drain();
        }
    }

    /**
     * @throws Error|Exception
     */
    private function drain()
    {
        for ($i = key($this->queue); isset($this->queue[$i]); $i++)
        {
            $cancellable = $this->queue[$i];
            $ex = null;

            try
            {
                $cancellable->cancel();
            }
            catch (Error $ex)
            {}
            catch (Exception $ex)
            {}

            unset($this->queue[$i]);

            if ($ex)
            {
                throw $ex;
            }
        }

        $this->queue = [];
    }
}
