<?php

namespace Kraken\_Unit\Console\Client\Command\Thread;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Thread\ThreadStopCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ThreadStopCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ThreadStopCommand::class;

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

        $this->assertCommand($command, 'thread:stop', '#^(.*?)$#si', $args, $opts);
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
            'thread:stop',
            [
                'alias' => 'alias'
            ]
        ];

        $this->assertSame($expected, $result);
    }
}
