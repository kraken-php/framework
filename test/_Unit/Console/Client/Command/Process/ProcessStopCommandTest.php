<?php

namespace Kraken\_Unit\Console\Client\Command\Process;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Process\ProcessStopCommand;
use Symfony\Component\Console\Input\InputArgument;

class ProcessStopCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProcessStopCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $args[] = [ 'parent', InputArgument::REQUIRED ];
        $args[] = [ 'alias',  InputArgument::REQUIRED ];

        $opts = [];

        $this->assertCommand($command, 'process:stop', '#^(.*?)$#si', $args, $opts);
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
                'parent',
                'process:stop',
                [
                    'alias' => 'alias'
                ]
            );

        $input  = $this->createInputMock();
        $output = $this->createOutputMock();

        $this->callProtectedMethod($command, 'command', [ $input, $output ]);
    }
}
