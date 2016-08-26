<?php

namespace Kraken\_Unit\Console\Client\Command\Container;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Container\ContainerStatusCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ContainerStatusCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerStatusCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $args[] = [ 'alias',  InputArgument::REQUIRED ];

        $opts = [];

        $this->assertCommand($command, 'container:status', '#^(.*?)$#si', $args, $opts);
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
            'alias',
            'container:status',
            []
        ];

        $this->assertSame($expected, $result);
    }
}
