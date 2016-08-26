<?php

namespace Kraken\_Unit\Console\Client\Command\Project;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Project\ProjectStatusCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
        $command  = $this->createCommand();
        $input    = $this->createInputMock();
        $output   = $this->createOutputMock();

        $result   = $this->callProtectedMethod($command, 'command', [ $input, $output ]);
        $expected = [
            null,
            'project:status',
            []
        ];

        $this->assertSame($expected, $result);
    }
}
