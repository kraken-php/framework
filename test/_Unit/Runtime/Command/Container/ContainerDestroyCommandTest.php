<?php

namespace Kraken\_Unit\Runtime\Command\Container;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Container\ContainerDestroyCommand;

class ContainerDestroyCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerDestroyCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $command = $this->createCommand();
        $runtime = $this->createRuntime([ 'destroy' ]);
        $runtime
            ->expects($this->once())
            ->method('destroy');

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $command, 'command', []
            )
        );
    }
}
