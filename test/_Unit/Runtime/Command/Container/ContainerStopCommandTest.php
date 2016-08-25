<?php

namespace Kraken\_Unit\Runtime\Command\Container;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Container\ContainerStopCommand;

class ContainerStopCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerStopCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $command = $this->createCommand();
        $runtime = $this->createRuntime([ 'stop' ]);
        $runtime
            ->expects($this->once())
            ->method('stop');

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $command, 'command', []
            )
        );
    }
}
