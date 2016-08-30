<?php

namespace Kraken\_Unit\Runtime\Command\Processes;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Processes\ProcessesGetCommand;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use StdClass;

class ProcessesGetCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProcessesGetCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $result = new StdClass;

        $command = $this->createCommand();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('getProcesses')
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $command, 'command', []
            )
        );
    }
}
