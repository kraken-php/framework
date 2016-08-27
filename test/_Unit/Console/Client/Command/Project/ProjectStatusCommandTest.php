<?php

namespace Kraken\_Unit\Console\Client\Command\Project;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Project\ProjectStatusCommand;

class ProjectStatusCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProjectStatusCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $opts = [];

        $this->assertCommand($command, 'project:status', '#^(.*?)$#si', $args, $opts);
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
                null,
                'project:status',
                []
            );

        $input  = $this->createInputMock();
        $output = $this->createOutputMock();

        $this->callProtectedMethod($command, 'command', [ $input, $output ]);
    }
}
