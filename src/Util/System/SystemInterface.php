<?php

namespace Kraken\Util\System;

interface SystemInterface
{
    /**
     * Run command asynchronously, and get pid of its process.
     *
     * @param string $command
     * @return string
     */
    public function run($command);

    /**
     * Kill process with given pid, returns whether operation was successful.
     *
     * @param string $pid
     * @return bool
     */
    public function kill($pid);

    /**
     * Checks if process with given pid exists, returns whether operation was successful.
     *
     * @param string $pid
     * @param bool $isolate
     * @return bool
     */
    public function existsPid($pid);
}
