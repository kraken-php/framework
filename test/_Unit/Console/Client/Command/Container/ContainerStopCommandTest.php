<?php

namespace Kraken\_Unit\Console\Client\Command\Container;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Container\ContainerStopCommand;
use Kraken\Runtime\Runtime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ContainerStopCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerStopCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $args[] = [ 'alias',  InputArgument::REQUIRED ];

        $opts = [];

        $this->assertCommand($command, 'container:stop', '#^(.*?)$#si', $args, $opts);
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
            'container:stop',
            []
        ];

        $this->assertSame($expected, $result);
    }
}
