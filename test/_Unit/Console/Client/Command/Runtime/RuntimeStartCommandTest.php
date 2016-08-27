<?php

namespace Kraken\_Unit\Console\Client\Command\Runtime;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Runtime\RuntimeStartCommand;
use Symfony\Component\Console\Input\InputArgument;

class RuntimeStartCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = RuntimeStartCommand::class;

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

        $this->assertCommand($command, 'runtime:start', '#^(.*?)$#si', $args, $opts);
    }

    /**
     *
     */
    public function testApiCommand_ReturnsCommandData()
    {
        $command  = $this->createCommand([ 'informServer' ]);
        $command
            ->expects($this->once())
            ->method('informServer')
            ->with(
                'parent',
                'runtime:start',
                [
                    'alias' => 'alias'
                ]
            );

        $input  = $this->createInputMock();
        $output = $this->createOutputMock();

        $this->callProtectedMethod($command, 'command', [ $input, $output ]);
    }
}
