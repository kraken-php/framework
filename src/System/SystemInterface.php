<?php

namespace Kraken\System;

interface SystemInterface
{
    /**
     * @param string $command
     * @return string
     */
    public function run($command);

    /**
     * @param string $pid
     * @return bool
     */
    public function kill($pid);

    /**
     * @param string $pid
     * @return bool
     */
    public function existsPid($pid);
}
