<?php

namespace Kraken\_Unit\Console\Client\Command\Process;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Process\ProcessExistsCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ProcessExistsCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProcessExistsCommand::class;

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

        $this->assertCommand($command, 'process:exists', '#^(.*?)$#si', $args, $opts);
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
            'process:exists',
            [
                'alias' => 'alias'
            ]
        ];

        $this->assertSame($expected, $result);
    }
}
