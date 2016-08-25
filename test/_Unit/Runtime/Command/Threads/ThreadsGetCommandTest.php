<?php

namespace Kraken\_Unit\Runtime\Command\Threads;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Threads\ThreadsGetCommand;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use StdClass;

class ThreadsGetCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ThreadsGetCommand::class;

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
            ->method('getThreads')
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $command, 'command', []
            )
        );
    }
}
