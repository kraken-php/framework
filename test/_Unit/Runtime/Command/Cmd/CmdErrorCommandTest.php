<?php

namespace Kraken\_Unit\Runtime\Command\Cmd;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Cmd\CmdErrorCommand;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Exception;

class CmdErrorCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = CmdErrorCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $exception = 'Exception';
        $message   = 'Reason';
        $origin    = 'Origin';

        $command = $this->createCommand();
        $manager = $this->createSupervisor();
        $manager
            ->expects($this->atLeastOnce())
            ->method('solve')
            ->with($this->isInstanceOf(Exception::class), [ 'origin' => $origin ]);

        $this->assertSame(
            null,
            $this->callProtectedMethod(
                $command, 'command', [[ 'exception' => $exception, 'message' => $message, 'origin' => $origin ]]
            )
        );
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamExceptionDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[ 'message' => 'message', 'origin' => 'origin' ]]);
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamMessageDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[ 'exception' => 'exception', 'origin' => 'origin' ]]);
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamOriginDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[ 'exception' => 'exception', 'message' => 'message' ]]);
    }
}
