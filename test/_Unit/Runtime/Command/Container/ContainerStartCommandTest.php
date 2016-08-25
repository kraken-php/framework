<?php

namespace Kraken\_Unit\Runtime\Command\Container;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Container\ContainerStartCommand;

class ContainerStartCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerStartCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $command = $this->createCommand();
        $runtime = $this->createRuntime([ 'start' ]);
        $runtime
            ->expects($this->once())
            ->method('start');

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $command, 'command', []
            )
        );
    }
}
