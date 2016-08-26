<?php

namespace Kraken\_Unit\Console\Client\Command\Thread;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Thread\ThreadCreateCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ThreadCreateCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ThreadCreateCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $args[] = [ 'parent', InputArgument::REQUIRED ];
        $args[] = [ 'alias',  InputArgument::REQUIRED ];
        $args[] = [ 'name',   InputArgument::REQUIRED ];

        $opts = [];
        $opts[] = [ 'flags', null, InputOption::VALUE_OPTIONAL, '#^(.*?)$#', Runtime::CREATE_DEFAULT ];

        $this->assertCommand($command, 'thread:create', '#^(.*?)$#si', $args, $opts);
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
            'thread:create',
            [
                'alias' => 'alias',
                'name'  => 'name',
                'flags' => 'flags'
            ]
        ];

        $this->assertSame($expected, $result);
    }
}
