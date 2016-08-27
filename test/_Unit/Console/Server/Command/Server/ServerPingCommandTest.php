<?php

namespace Kraken\_Unit\Console\Server\Command\Server;

use Kraken\_Unit\Console\Server\_T\TCommand;
use Kraken\Console\Server\Command\Server\ServerPingCommand;

class ServerPingCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ServerPingCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $command = $this->createCommand();

        $result = $this->callProtectedMethod(
            $command, 'command', []
        );

        $this->assertSame(gethostbyname(gethostname()), $result);
    }
}
