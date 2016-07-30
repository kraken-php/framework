<?php

namespace Kraken\Util\System;

class SystemUnix implements SystemInterface
{
    /**
     * @var string
     */
    protected $executor = 'exec';

    /**
     * @override
     * @inheritDoc
     */
    public function run($command)
    {
        return call_user_func($this->executor, $command . ' >/dev/null 2>&1 & echo $!');
    }

    /**
     * @override
     * @inheritDoc
     */
    public function kill($pid)
    {
        $output = $result = null;

        call_user_func($this->executor, 'kill -9 ' . $pid . ' >/dev/null 2>&1', $output, $result);

        return $result == 0;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsPid($pid)
    {
        $output = $result = null;

        call_user_func($this->executor, 'kill -0 ' . $pid . ' >/dev/null 2>&1', $output, $result);

        return $result == 0;
    }
}
