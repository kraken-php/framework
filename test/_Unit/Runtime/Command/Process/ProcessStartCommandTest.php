<?php

namespace Kraken\_Unit\Runtime\Command\Process;

use Kraken\_Unit\Runtime\Command\_T\TCommand;
use Kraken\Runtime\Command\Process\ProcessStartCommand;
use Kraken\Throwable\Exception\Runtime\Execution\RejectionException;
use StdClass;

class ProcessStartCommandTest extends TCommand
{
    /**
     * @var string
     */
    protected $class = ProcessStartCommand::class;

    /**
     *
     */
    public function testApiCommand_InvokesProperAction()
    {
        $alias  = 'alias';
        $result = new StdClass;

        $command = $this->createCommand();
        $manager = $this->createManager();
        $manager
            ->expects($this->once())
            ->method('startProcess')
            ->with($alias)
            ->will($this->returnValue($result));

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $command, 'command', [[ 'alias' => $alias ]]
            )
        );
    }

    /**
     *
     */
    public function testApiCommand_ThrowsException_WhenContextParamAliasDoesNotExist()
    {
        $this->setExpectedException(RejectionException::class);
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'command', [[]]);
    }
}
