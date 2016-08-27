<?php

namespace Kraken\Util\System;

class SystemUnix implements SystemInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function run($command)
    {
        return exec($command . ' >/dev/null 2>&1 & echo $!');
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
