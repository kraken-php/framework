<?php

namespace Kraken\_Unit\Runtime\Command\Container;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Container\ContainerStatusCommand;

class ContainerStatusCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ContainerStatusCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $command  = $this->createCommand();
        $expected = [
            'parent' => 'parent',
            'alias'  => 'alias',
            'name'   => 'name',
            'state'  => 2
        ];

        $this->assertSame(
            $expected,
            $this->callProtectedMethod(
                $command, 'command', []
            )
        );
    }
}
