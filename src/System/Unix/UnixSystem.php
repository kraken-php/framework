<?php

namespace Kraken\System\Unix;

use Kraken\System\SystemInterface;

class UnixSystem implements SystemInterface
{
    /**
     * Run command asynchronously, and get pid of its process.
     *
     * @param string $command
     * @return string
     */
    public function run($command)
    {
        return exec($command . ' >/dev/null 2>&1 & echo $!');
    }

    /**
     * Kill process with given pid, returns whether operation was successful.
     *
     * @param string $pid
     * @return bool
     */
    public function kill($pid)
    {
        exec('kill -9 ' . $pid . ' >/dev/null 2>&1', $output, $result);

        return $result == 0;
    }

    /**
     * Checks if process with given pid exists, returns whether operation was successful.
     *
     * @param string $pid
     * @return bool
     */
    public function existsPid($pid)
    {
        exec('kill -0 ' . $pid . ' >/dev/null 2>&1', $output, $result);

        return $result == 0;
    }
}
