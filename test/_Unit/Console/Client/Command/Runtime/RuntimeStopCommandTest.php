<?php

namespace Kraken\_Unit\Console\Client\Command\Runtime;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Runtime\RuntimeStopCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RuntimeStopCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = RuntimeStopCommand::class;

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

        $this->assertCommand($command, 'runtime:stop', '#^(.*?)$#si', $args, $opts);
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
            'parent',
            'runtime:stop',
            [
                'alias' => 'alias'
            ]
        ];

        $this->assertSame($expected, $result);
    }
}
