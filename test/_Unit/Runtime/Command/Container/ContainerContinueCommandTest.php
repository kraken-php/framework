<?php

namespace Kraken\_Unit\Runtime\Command\Container;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Container\ContainerContinueCommand;

class ContainerContinueCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerContinueCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $command = $this->createCommand();
        $runtime = $this->createRuntime([ 'succeed' ]);
        $runtime
            ->expects($this->once())
            ->method('succeed');

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $command, 'command', []
            )
        );
    }
}
