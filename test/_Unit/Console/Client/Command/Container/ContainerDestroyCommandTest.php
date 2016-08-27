<?php

namespace Kraken\_Unit\Console\Client\Command\Container;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Container\ContainerDestroyCommand;
use Symfony\Component\Console\Input\InputArgument;

class ContainerDestroyCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerDestroyCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $args[] = [ 'alias',  InputArgument::REQUIRED ];

        $opts = [];

        $this->assertCommand($command, 'container:destroy', '#^(.*?)$#si', $args, $opts);
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
                'container:destroy',
                []
            );

        $input    = $this->createInputMock();
        $output   = $this->createOutputMock();

        $this->callProtectedMethod($command, 'command', [ $input, $output ]);
    }
}
