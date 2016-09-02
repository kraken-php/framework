<?php

namespace Kraken\_Unit\Runtime\Command\Cmd;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Cmd\CmdPingCommand;

class CmdPingCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = CmdPingCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $command  = $this->createCommand();
        $expected = gethostbyname(gethostname());

        $this->assertSame(
            $expected,
            $this->callProtectedMethod(
                $command, 'command', []
            )
        );
    }
}
