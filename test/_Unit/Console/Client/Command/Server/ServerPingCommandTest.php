<?php

namespace Kraken\_Unit\Console\Client\Command\Ping;

use Kraken\_Unit\Console\Client\_T\TCommand;
use Kraken\Console\Client\Command\Server\ServerPingCommand;

class ServerPingCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ServerPingCommand::class;

    /**
     *
     */
    public function testApiOnMessage_FiltersMessage()
    {
        $command = $this->createCommand();

        $this->assertSame('ip=127.0.0.1', $this->callProtectedMethod($command, 'onMessage', [ '127.0.0.1' ]));
    }

    /**
     *
     */
    public function testApiConfig_ConfiguresCommand()
    {
        $command = $this->createCommand();

        $args = [];
        $opts = [];

        $this->assertCommand($command, 'server:ping', '#^(.*?)$#si', $args, $opts);
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
                null,
                'server:ping',
                []
            );

        $input  = $this->createInputMock();
        $output = $this->createOutputMock();

        $this->callProtectedMethod($command, 'command', [ $input, $output ]);
    }
}
