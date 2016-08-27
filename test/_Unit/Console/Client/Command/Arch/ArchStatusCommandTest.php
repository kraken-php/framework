<?php

namespace Kraken\_Unit\Console\Client\Command\Arch;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Arch\ArchStatusCommand;
use Symfony\Component\Console\Input\InputArgument;

class ArchStatusCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ArchStatusCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $args[] = [ 'alias',  InputArgument::REQUIRED ];

        $opts = [];

        $this->assertCommand($command, 'arch:status', '#^(.*?)$#si', $args, $opts);
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
                'arch:status',
                []
            );

        $input    = $this->createInputMock();
        $output   = $this->createOutputMock();

        $this->callProtectedMethod($command, 'command', [ $input, $output ]);
    }
}
