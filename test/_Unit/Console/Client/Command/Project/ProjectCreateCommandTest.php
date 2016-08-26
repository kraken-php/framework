<?php

namespace Kraken\_Unit\Console\Client\Command\Project;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Project\ProjectCreateCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ProjectCreateCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProjectCreateCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];

        $opts = [];
        $opts[] = [ 'flags', null, InputOption::VALUE_OPTIONAL, '#^(.*?)$#', Runtime::CREATE_DEFAULT ];

        $this->assertCommand($command, 'project:create', '#^(.*?)$#si', $args, $opts);
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
            'project:create',
            [
                'flags' => 'flags'
            ]
        ];

        $this->assertSame($expected, $result);
    }
}
