<?php

namespace Kraken\Util\System;

use Kraken\Util\Isolate\IsolateInterface;

class SystemUnix implements SystemInterface
{
    /**
     * @var IsolateInterface|null
     */
    protected $isolator;

    /**
     * @param IsolateInterface|null $isolator
     */
    public function __construct(IsolateInterface $isolator = null)
    {
        $this->isolator = $isolator;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->isolator);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function run($command)
    {
        $command = $command . '>/dev/null 2>&1 & echo $!';

        if ($this->isolator !== null)
        {
            return $this->isolator->call('exec', [ $command ]);
        }
        else
        {
            return exec($command);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function kill($pid)
    {
        exec('kill -9 ' . $pid . ' >/dev/null 2>&1', $output, $result);

        return $result == 0;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsPid($pid)
    {
        exec('kill -0 ' . $pid . ' >/dev/null 2>&1', $output, $result);

        return $result == 0;
    }
}
