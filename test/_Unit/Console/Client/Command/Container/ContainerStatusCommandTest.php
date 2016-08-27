<?php

namespace Kraken\_Unit\Console\Client\Command\Container;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Container\ContainerStatusCommand;
use Symfony\Component\Console\Input\InputArgument;

class ContainerStatusCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerStatusCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $args[] = [ 'alias',  InputArgument::REQUIRED ];

        $opts = [];

        $this->assertCommand($command, 'container:status', '#^(.*?)$#si', $args, $opts);
    }

    /**
     *
     */
    public function testApiCommand_ReturnsCommandData()
    {
        $command  = $this->createCommand([ 'informServer' ]);
        $command
            ->expects($this->once())
            ->method('informServer')
            ->with(
                'alias',
                'container:status',
                []
            );

        $input    = $this->createInputMock();
        $output   = $this->createOutputMock();

        $this->callProtectedMethod($command, 'command', [ $input, $output ]);
    }
}
