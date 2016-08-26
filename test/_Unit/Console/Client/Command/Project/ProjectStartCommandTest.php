<?php

namespace Kraken\_Unit\Console\Client\Command\Project;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Project\ProjectStartCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ProjectStartCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProjectStartCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $opts = [];

        $this->assertCommand($command, 'project:start', '#^(.*?)$#si', $args, $opts);
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
            'project:start',
            []
        ];

        $this->assertSame($expected, $result);
    }
}
