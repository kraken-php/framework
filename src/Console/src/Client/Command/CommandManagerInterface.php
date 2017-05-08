<?php

namespace Kraken\Console\Client\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

interface CommandManagerInterface
{
    /**
     * Set framework version to print.
     *
     * @param string $version
     */
    public function setVersion($version);

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     * @return int 0 if everything went fine, or an error code
     * @throws Exception When doRun returns Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null);
}
