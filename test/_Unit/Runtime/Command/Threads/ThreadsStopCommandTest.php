<?php

namespace Kraken\_Unit\Runtime\Command\Threads;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Threads\ThreadsStopCommand;
use Dazzle\Throwable\Exception\Runtime\RejectionException;
use StdClass;

class ThreadsStopCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ThreadsStopCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $aliases = [ 'alias1', 'alias2' ];
        $result  = new StdClass;

        $command = $this->createCommand();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('stopThreads')
            ->with($aliases)
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $command, 'command', [[ 'aliases' => $aliases ]]
            )
        );
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamAliasesDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[]]);
    }
}
