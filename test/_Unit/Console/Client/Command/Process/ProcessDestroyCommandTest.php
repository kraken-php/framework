<?php

namespace Kraken\_Unit\Console\Client\Command\Process;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Process\ProcessDestroyCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ProcessDestroyCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProcessDestroyCommand::class;

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
        $opts[] = [ 'flags', null, InputOption::VALUE_OPTIONAL, '#^(.*?)$#', Runtime::DESTROY_FORCE_SOFT ];

        $this->assertCommand($command, 'process:destroy', '#^(.*?)$#si', $args, $opts);
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
            'process:destroy',
            [
                'alias' => 'alias',
                'flags' => 'flags'
            ]
        ];

        $this->assertSame($expected, $result);
    }
}
