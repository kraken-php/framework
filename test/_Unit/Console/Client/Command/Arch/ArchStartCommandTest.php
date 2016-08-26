<?php

namespace Kraken\_Unit\Console\Client\Command\Arch;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Arch\ArchStartCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ArchStartCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ArchStartCommand::class;

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $args[] = [ 'alias',  InputArgument::REQUIRED ];

        $opts = [];

        $this->assertCommand($command, 'arch:start', '#^(.*?)$#si', $args, $opts);
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
            'arch:start',
            []
        ];

        $this->assertSame($expected, $result);
    }
}
