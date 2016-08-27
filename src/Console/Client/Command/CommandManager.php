<?php

namespace Kraken\Console\Client\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

class CommandManager extends Application implements CommandManagerInterface
{
    /**
     * @var bool
     */
    private $async = false;

    /**
     * @var string
     */
    private $version = '';

    /**
     * @override
     * @inheritDoc
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        printf("Kraken %s by Kamil Jamroz and contributors.\n\n", $this->version);

        $this->async = false;

        $code = parent::run($input, $output);

        if ($this->async !== true)
        {
            exit($code);
        }

        return $code;
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function doRunCommand(SymfonyCommand $command, InputInterface $input, OutputInterface $output)
    {
        if ($command instanceof CommandInterface && $command->isAsync() === true)
        {
            $this->async = true;
        }

        return parent::doRunCommand($command, $input, $output);
    }
}
